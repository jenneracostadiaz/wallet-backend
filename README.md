# Wallet v3

A personal finance system that helps you track your accounts, transactions, and categories.

## Features

- Multiple account management
- Transaction tracking
- Category management
- User authentication
- Multi-currency support

## Currency Management

The system supports multiple currencies through the Currency model. Each account is associated with a specific currency.

### Available Currencies

The system comes with several pre-configured currencies:

- USD (US Dollar)
- EUR (Euro)
- GBP (British Pound)
- JPY (Japanese Yen)
- CAD (Canadian Dollar)
- AUD (Australian Dollar)
- CHF (Swiss Franc)
- CNY (Chinese Yuan)

## Technical Documentation

### Models

#### Account
- Fields: name, type, balance, currency_id, user_id, description, color, order
- Relationships: belongs to Currency, belongs to User

#### Category
- Fields: name, type, icon, parent_id, user_id, order
- Relationships: belongs to User, supports parent/subcategory hierarchy

#### Currency
- Fields: code, name, symbol, decimal_places
- No timestamps

#### Transaction
- Fields: amount, description, date, type, account_id, category_id, to_account_id, user_id
- Relationships: belongs to Account, Category, User

#### User
- Fields: name, email, password
- Relationships: has many Accounts, has many Categories

### API Endpoints

#### Authentication
- POST /register: Register a new user
- POST /login: Login
- POST /forgot-password: Request password reset
- GET /user: Get authenticated user info
- POST /logout: Logout
- POST /logout-all: Logout from all sessions
- POST /refresh-token: Refresh authentication token

#### Currency
- GET /currencies: List all currencies
- GET /currencies/{id}: Get currency details

#### Account
- GET /accounts: List accounts
- POST /accounts: Create account
- GET /accounts/{id}: Get account details
- PUT /accounts/{id}: Update account
- DELETE /accounts/{id}: Delete account
- GET /accounts/{id}/export-pdf: Export account as PDF
- GET /accounts/{id}/export-csv: Export account as CSV

#### Category
- GET /categories: List categories
- POST /categories: Create category
- GET /categories/{id}: Get category details
- PUT /categories/{id}: Update category
- DELETE /categories/{id}: Delete category

#### Transaction
- GET /transactions: List transactions
- POST /transactions: Create transaction
- GET /transactions/{id}: Get transaction details
- PUT /transactions/{id}: Update transaction
- DELETE /transactions/{id}: Delete transaction
- GET /transactions/export-csv: Export transactions as CSV

#### Dashboard
- GET /dashboard: Main dashboard info
- GET /dashboard/balance: Get balance summary
- GET /dashboard/monthly-report: Monthly report
- GET /dashboard/latest-transactions: Latest transactions
- GET /dashboard/monthly-comparison: Monthly comparison
- GET /dashboard/quick-stats: Quick stats
