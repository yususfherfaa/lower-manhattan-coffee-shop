# Changelog

## Unreleased — verifikasi akhir PR-13–PR-22

- Added cancellation and expiry handling for unpaid orders, including stock restoration within a transaction and a manual migration file.
- Added cashier management for soft-deactivated product options, including reactivation and CSRF-protected POST actions.
- Added read-only export and evaluation support for paid database transactions; database results are written separately from synthetic ML artifacts.
- Added best-effort recommendation impression and selection logs without customer identity, a manual schema file, and an admin acceptance report by hour.
- Added PHPUnit unit coverage for checkout and payment rules, Flask client success/failure handling, and cashier denial by the admin role filter.
- Hardened configuration, documented the synthetic dataset limitations, and completed the PR-21 code audit.

### Verification notes

- `composer install --no-interaction --prefer-dist` completed; no packages changed. Composer reported PSR-4 warnings for legacy FPDF files in `app/ThirdParty/fpdf/`.
- `vendor/bin/phpunit -c phpunit.xml.dist --no-coverage` passed 10 tests and 14 assertions. The tests do not connect to an application database.
- Python dependencies from `requirements.txt` were installed in `.venv-ml` using Python 3.12.6. The synthetic ML pipeline completed, and the Flask test client returned 200 for a valid request and 400 for an invalid hour.
- The active `warkop_bahagia` database was backed up to the system temporary directory before applying its remaining migrations: order access tokens, security constraints, status cleanup/cancellation, and recommendation logs. The potentially stock-resetting variant seed script was not rerun.
- A clone named `warkop_bahagia_test` was created first. PHP migrations and the PR-03, PR-09, PR-13, PR-16, and PR-20 SQL migrations were validated there before updating the active database.
- On the temporary app pointed at the clone, menu rendering, checkout, cash payment, pending-order cancellation with stock restoration, required option selection, recommendation impression/selection, and the admin acceptance report succeeded. Test orders and accounts were removed afterward.
- Security checks confirmed local HTTP cookies no longer trigger the Secure-cookie exception, and `nosniff`, `SAMEORIGIN`, and `same-origin` headers were returned. A customer-name XSS payload was escaped. A filled honeypot blocked login with HTTP 400.
- DB export ran read-only and returned 15 paid order items. The DB ML evaluation correctly stopped because the clone does not meet the 30-day and 2,000-row minimum.
- After the active database migration, `/order/MEJA001` returned HTTP 200. A temporary Flask process and the live PHP recommendation endpoint returned five recommendations (`knn-waktu`); Flask was stopped after the check.
- MariaDB reported a corrupt `global_priv` index when a temporary read-only server account was attempted. No system-table repair was attempted. No release tag was created.

See [STATUS_PR-01-21.md](STATUS_PR-01-21.md) for per-PR completion details and remaining verification.
