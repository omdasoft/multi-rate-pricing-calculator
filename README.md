# Multi-Rate Pricing Calculator — Laravel API

A REST API for creating documents with line items, applying per-line
discounts and tax, and reporting totals over a date range.

## Live URL

- **Deployed API:** [Insert public URL here]

## Stack

- Laravel 13, PHP 8.3+
- Laravel Sanctum (token auth)
- MySQL (or SQLite for local/testing)
- PHPUnit

## Architecture

The code favours a small number of clear layers over any framework
magic:

```
app/Domain/Pricing/          Pure calculation module — no Eloquent, no I/O.
                              LineItemCalculator + DocumentTotalsCalculator.
                              This is the one place pricing math lives.
app/DTOs/     Readonly DTOs that carry validated request
                              data into Actions. Keeps Actions decoupled
                              from Illuminate\Http\Request.
app/Actions/                 One class, one execute() method, one job:
                              CreateDocumentAction, AddLineItemAction,
                              FinalizeDocumentAction, etc. Controllers are
                              thin — they validate, call an Action, return
                              a Resource.
app/Http/Requests/           Validation + specific error messages.
app/Http/Resources/          API response shape (cents -> decimal dollars).
app/Policies/                Per-user data isolation (DocumentPolicy).
app/Models/                  Eloquent models, thin — no business logic.
```

This mirrors the Action + DTO pattern deliberately: it's easy to unit
test (Actions take plain DTOs, not requests), each class has one reason
to change, and there's no repository/service layer ceremony on top of
Eloquent — for an app this size that would be over-engineering.

## Setup

To run this project locally, simply clone the repository and install the dependencies:

```bash
# Clone the repository
git clone <your-repo-url>
cd multi-rate-pricing-calculator

# Install PHP dependencies
composer install

# Set up your environment variables
cp .env.example .env
php artisan key:generate

# Configure your database in the .env file. 
# For a quick local setup using SQLite:
# DB_CONNECTION=sqlite
# touch database/database.sqlite

# Run the database migrations
php artisan migrate

# Start the local development server
php artisan serve
```

### Running tests

```bash
php artisan test
```

The calculation module's tests (`tests/Unit/Pricing/`) extend plain
`PHPUnit\Framework\TestCase`, not Laravel's — they don't touch the
database or the framework, so they run in milliseconds. This is
deliberate: it's the highest-value test surface in the assignment, so
it should be trivial to run constantly.

## Calculation & rounding policy

All money is stored and computed as **integer cents** — never floats —
to avoid floating-point drift. Percentages (`discount_percent`,
`tax_percent`) are stored as `decimal(5,2)`.

**Rounding**: round **half up** (PHP's default `round()` mode — half
away from zero), applied **once per line, per step**, to the nearest
cent. There is no second rounding pass at the document level — document
totals are a plain sum of already-rounded line results, which is why a
grand total always ties out to `subtotal - discount + tax` on the nose.

Per line, in order:

1. `subtotal = quantity × unit_price`
2. `discount = fixed amount` **or** `round(subtotal × percent / 100)` — not both
3. `after_discount = subtotal - discount`
4. `tax = round(after_discount × tax_percent / 100)`
5. `line_total = after_discount + tax`

### Worked example (matches the assignment's sample table)

| Line | Qty | Unit price | Discount | Tax | Subtotal | Discount amt | After discount | Tax amt | Line total |
|---|---|---|---|---|---|---|---|---|---|
| Widget A | 2 | $100.00 | 10% | 5% | $200.00 | $20.00 | $180.00 | $9.00 | $189.00 |
| Widget B | 1 | $50.00 | — | 5% | $50.00 | $0.00 | $50.00 | $2.50 | $52.50 |
| Service fee | 1 | $200.00 | $20 fixed | — | $200.00 | $20.00 | $180.00 | $0.00 | $180.00 |

Document totals: subtotal **$450.00**, total discount **$40.00**, total
tax **$11.50**, grand total **$421.50**. This exact table is asserted in
`tests/Unit/Pricing/LineItemCalculatorTest.php`,
`DocumentTotalsCalculatorTest.php`, and end-to-end in
`tests/Feature/DocumentLifecycleTest.php`.

## Finalize / immutability rules

- A document is `draft` or `finalized`.
- All line item and document-metadata writes require `draft`. Any write
  against a `finalized` document throws `DocumentIsFinalizedException`,
  mapped to a `422` with a clear message.
- `POST /documents/{id}/finalize` transitions `draft -> finalized` and
  stamps `finalized_at`. It's rejected if the document has zero lines,
  or if any line has `quantity <= 0` or a negative price (stretch goal).
- **Duplicate (stretch goal)**: `POST /documents/{id}/duplicate` copies
  any document (draft or finalized) into a brand-new `draft`, lines
  included. The copy's cached totals are copied verbatim rather than
  recalculated, since the underlying lines are copied unchanged.

## Assumptions & tradeoffs

- **Fixed discount exceeding the line subtotal is rejected, not
  clamped.** A silently-clamped discount can hide a data-entry mistake
  (typing `2000` instead of `20.00`); a 422 surfaces it immediately.
  This is enforced in the domain layer (`LineItemCalculator`) so it
  can never be bypassed by a different code path.
- **A line may have a percent discount or a fixed discount, not both**,
  enforced via Laravel's `prohibits` validation rule plus a domain
  exception as a second line of defense.
- **Quantity/unit price/percent fields are validated on the request**,
  not the database — kept the schema simple rather than duplicating
  constraints in both places.
- **Login always runs `Hash::check` even for an unknown email**, so a
  wrong email and a wrong password fail in the same amount of time
  (avoids trivial email enumeration via timing).
- **Cached totals**: `documents` and `line_items` store their computed
  cents alongside the raw inputs, recomputed server-side on every
  write (`RecalculateDocumentTotalsAction`). This trades a small
  amount of write-time work for cheap reads and a simple summary
  report query — no need to re-walk every line on every list/report
  request.
- Quantity is stored as an integer (the sample data and spec only use
  whole units); fractional quantities aren't supported.

## What I'd improve before production

- Add database-level constraints (e.g. a check constraint or trigger)
  as a second line of defense against the "not both discount types"
  rule, in case a future code path writes to the table directly.
- Move the cents/dollars conversion in `LineItemData::fromArray()` into
  a small dedicated value object if more money-bearing fields get added
  — right now it's a couple of `round(... * 100)` calls, which is fine
  at this size but wouldn't scale past it.
- Add rate limiting on `/register`.
- Paginate/limit the summary report's date range, and consider a
  materialized daily rollup if document volume grows large.
- Printable view (HTML/PDF export) — left out as an optional stretch
  goal; `DocumentResource` already has everything a template would need.
