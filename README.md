# Multi-Rate Pricing Calculator — Laravel API

A REST API for creating and managing documents with line items, applying per-line discounts and tax, calculating totals, and generating summary reports over a date range.

The current implementation focuses on the backend API and core business logic. The frontend dashboard is intentionally omitted from this submission. The API is designed to be consumed by a separate frontend application, such as a Vue.js or React dashboard, which can be added later.

## Live URL

* **Deployed API:** https://task.omdasoft.dev

## Stack

* Laravel 13, PHP 8.3+
* Laravel Sanctum (token authentication)
* MySQL (or SQLite for local/testing)
* PHPUnit

## Architecture

This project follows an API-first approach. The backend is intentionally separated from the frontend so that the API can serve a dedicated web dashboard or other clients in the future.

The code favours a small number of clear layers over unnecessary framework abstraction:

```text
app/Domain/Pricing/          Pure calculation module — no Eloquent, no I/O.
                             LineItemCalculator + DocumentTotalsCalculator.
                             This is the one place pricing math lives.

app/DTOs/                    Readonly DTOs that carry validated request
                             data into Actions.

app/Actions/                 One class, one execute() method, one job:
                             CreateDocumentAction, AddLineItemAction,
                             FinalizeDocumentAction, etc.

app/Http/Requests/           Validation + specific error messages.

app/Http/Resources/          API response shape.

app/Policies/                Per-user data isolation.

app/Models/                  Eloquent models, kept thin with business
                             logic handled by the appropriate domain/actions.
```

This separation keeps the pricing logic independent from HTTP, Eloquent, and the eventual frontend implementation. It also allows the same API to be consumed by a Vue.js, React, mobile, or other client in the future.

## Frontend

The frontend dashboard is not included in this submission.

I intentionally separated the API from the frontend rather than building the dashboard inside the Laravel application. This keeps the backend independently deployable and allows the API to serve a dedicated frontend application.

A future frontend can consume the existing REST API to provide:

* Authentication
* Document creation and editing
* Line-item management
* Document finalization
* Document listing and filtering
* Date-range summary reports
* Read-only views for finalized documents

Vue.js would be the preferred choice for the dashboard, although the API is framework-agnostic and can also be consumed by React or other clients.

## Setup

To run this project locally, clone the repository and install the dependencies:

```bash
# Clone the repository
git clone git@github.com:omdasoft/multi-rate-pricing-calculator.git
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

## Running Tests

```bash
php artisan test
```

The calculation module's tests (`tests/Unit/Pricing/`) extend plain `PHPUnit\Framework\TestCase`, not Laravel's. They don't touch the database or the framework, so they run quickly.

This is deliberate because the calculation module is the highest-value test surface in this assignment.

## Calculation & Rounding Policy

All money is stored and computed as **integer cents** — never floats — to avoid floating-point drift. Percentages (`discount_percent`, `tax_percent`) are stored as `decimal(5,2)`.

**Rounding:** round **half up** to the nearest cent. Rounding is applied consistently during percentage-based discount and tax calculations. Document totals are calculated by summing the resulting line values, with no additional document-level rounding.

Per line, in order:

1. `subtotal = quantity × unit_price`
2. `discount = fixed amount` **or** `round(subtotal × percent / 100)` — not both
3. `after_discount = subtotal - discount`
4. `tax = round(after_discount × tax_percent / 100)`
5. `line_total = after_discount + tax`

## Worked Example

The implementation matches the assignment's sample calculation:

| Line        | Qty | Unit price | Discount  | Tax | Subtotal | Discount amt | After discount | Tax amt | Line total |
| ----------- | --: | ---------: | --------- | --- | -------: | -----------: | -------------: | ------: | ---------: |
| Widget A    |   2 |    $100.00 | 10%       | 5%  |  $200.00 |       $20.00 |        $180.00 |   $9.00 |    $189.00 |
| Widget B    |   1 |     $50.00 | —         | 5%  |   $50.00 |        $0.00 |         $50.00 |   $2.50 |     $52.50 |
| Service fee |   1 |    $200.00 | $20 fixed | —   |  $200.00 |       $20.00 |        $180.00 |   $0.00 |    $180.00 |

Document totals:

* Subtotal: **$450.00**
* Total discount: **$40.00**
* Total tax: **$11.50**
* Grand total: **$421.50**

## Finalize / Immutability Rules

* A document is `draft` or `finalized`.
* All line-item and document-metadata writes require `draft`.
* Any write against a `finalized` document is rejected with a clear `422` error.
* `POST /documents/{id}/finalize` transitions a document from `draft` to `finalized`.
* Finalized documents cannot be modified through the API.
* **Duplicate (stretch goal):** finalized documents can be duplicated into a new draft. The implementation also permits duplicating drafts for convenience.

## Assumptions & Tradeoffs

* **Fixed discount exceeding the line subtotal is rejected, not clamped.** A silently clamped discount could hide a data-entry mistake, so the API returns a validation error instead.
* **A line may have a percentage discount or a fixed discount, not both.** This is enforced during request validation and again in the domain layer.
* **Quantity/unit price/percentage fields are validated at the application layer.**
* **Cached totals:** documents and line items store their computed cents alongside the raw inputs. Totals are recalculated server-side on every write, providing cheap reads and a simple summary report query.
* **Quantity is stored as an integer**, since the assignment only specifies whole units.

## What I'd Improve Before Production

Before considering the application production-ready, I would address the following:

### Frontend dashboard

Build a dedicated **Vue.js frontend** that consumes the REST API.

The dashboard would provide the user interface for:

* Authentication
* Creating and editing draft documents
* Managing line items
* Applying discounts and taxes
* Finalizing documents
* Viewing finalized documents
* Filtering documents
* Viewing the date-range summary report

The frontend would remain separate from the Laravel API, allowing both applications to be developed, tested, and deployed independently.

### Other production improvements

* Add database-level constraints as a second line of defense for important business rules.
* Add rate limiting on `/register`.
* Move money conversion into a dedicated value object if more money-bearing fields are introduced.
* Improve report scalability for very large datasets, potentially with daily rollups.
* Add printable HTML/PDF document output.
* Add additional integration and authorization tests.
* Add API documentation, such as OpenAPI/Swagger, for easier frontend integration.
