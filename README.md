# Wallet v3

A personal finance system that helps you track your accounts, transactions, and categories.

## Features

- Multiple account management
- Transaction tracking
- Category management
- User authentication
- Multi-currency support
- **Scheduled Payments System** (NEW)
  - Recurring payments (subscriptions, services)
  - Debt management with installment tracking
  - One-time scheduled payments
  - Payment history and monitoring

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

## Scheduled Payments System

The wallet includes a comprehensive scheduled payments system that allows users to manage recurring payments, debts, and one-time scheduled payments.

### Payment Types

#### 1. Recurring Payments (`recurring`)
- Subscriptions (Netflix, Spotify, Disney+, etc.)
- Regular services (Internet, Cable, Utilities)
- Automatic frequency-based processing
- Support for daily, weekly, monthly, and yearly frequencies

#### 2. Debt Management (`debt`)
- Personal loans and credit cards
- Installment payments with interest tracking
- Progress monitoring (paid vs remaining amount)
- Creditor information and reference numbers
- Late fee and overdue tracking

#### 3. One-time Scheduled Payments (`one_time`)
- Utility bills with specific due dates
- Tax payments and licenses
- School fees and tuition
- Future payment scheduling

### Payment Status Management

- **Active**: Payment is scheduled and will be processed
- **Paused**: Temporarily suspended payments
- **Completed**: All payments finished
- **Cancelled**: Payment series terminated
- **Overdue**: Payment past due date

### Key Features

- **Flexible Scheduling**: Configure payments with various frequencies and intervals
- **Payment History**: Complete tracking of all payment attempts and results
- **Debt Progress**: Visual progress tracking for debt payments
- **Smart Notifications**: Configurable reminders before due dates
- **Metadata Support**: Store additional payment information in JSON format
- **Multi-currency**: Support for payments in different currencies

### Payment History Tracking

Every payment execution is tracked with:
- Planned vs actual amount
- Payment status (paid, failed, skipped, partial)
- Processing dates and times
- Related transaction references
- Failure reasons and notes

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

#### Scheduled Payment Models

##### ScheduledPayment
- Fields: name, description, payment_type, status, amount, color, icon, start_date, next_payment_date, end_date, user_id, account_id, category_id, metadata, order
- Relationships: belongs to User, Account, Category; has one PaymentSchedule, DebtDetail; has many PaymentHistory
- Scopes: active, paused, completed, overdue, recurring, debts, oneTime, upcoming, dueToday

##### PaymentSchedule
- Fields: scheduled_payment_id, frequency, interval, day_of_month, day_of_week, max_occurrences, occurrences_count, auto_process, create_transaction, days_before_notification
- Relationships: belongs to ScheduledPayment
- Methods: calculateNextPaymentDate, incrementOccurrences, canProcessNextPayment

##### DebtDetail
- Fields: scheduled_payment_id, original_amount, remaining_amount, paid_amount, total_installments, paid_installments, installment_amount, interest_rate, creditor, reference_number, due_date, late_fee, days_overdue
- Relationships: belongs to ScheduledPayment
- Methods: markInstallmentPaid, calculateNextDueDate, recalculateInstallmentAmount

##### PaymentHistory
- Fields: scheduled_payment_id, amount, planned_amount, status, scheduled_date, processed_date, transaction_id, failure_reason, notes
- Relationships: belongs to ScheduledPayment, belongs to Transaction (optional)
- Scopes: successful, failed, pending

#### Enums

##### PaymentType
- `recurring`: Recurring payments and subscriptions
- `debt`: Debt payments with installments
- `one_time`: One-time scheduled payments

##### PaymentStatus
- `active`: Payment is active and scheduled
- `paused`: Payment is temporarily paused
- `completed`: All payments finished
- `cancelled`: Payment series cancelled
- `overdue`: Payment is past due

##### PaymentFrequency
- `daily`: Daily payments
- `weekly`: Weekly payments
- `monthly`: Monthly payments
- `yearly`: Yearly payments

##### PaymentHistoryStatus
- `paid`: Payment successfully processed
- `pending`: Payment scheduled but not processed
- `failed`: Payment processing failed
- `skipped`: Payment intentionally skipped
- `partial`: Partial payment made

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
- GET /dashboard: Main dashboard info with complete financial overview
- GET /dashboard/balance: Get current balance summary by currency
- GET /dashboard/monthly-report: Detailed monthly financial report with expense categories
- GET /dashboard/latest-transactions: Recent transaction history with full details
- GET /dashboard/monthly-comparison: Compare current month vs previous month metrics
- GET /dashboard/quick-stats: Quick statistics (transactions count, accounts, categories)

### Dashboard Features

The dashboard provides comprehensive financial insights:

#### Balance Summary
- Total balance across all accounts
- Multi-currency support with separate totals
- Account count by currency
- Detailed account breakdown with current balances

#### Monthly Reports
- Income, expenses, and transfers breakdown
- Net income calculation
- Transaction count by period
- Top expense categories with percentages
- Daily balance trends throughout the month

#### Transaction Analytics
- Latest transactions with full relationship data
- Monthly comparison with percentage changes
- Quick statistics for different time periods
- Export capabilities for detailed analysis

#### Data Export
- **Account Summary**: PDF and CSV export for individual accounts
- **Transaction Export**: CSV export with filtering by category and account
- **Dashboard Reports**: Complete financial reports with charts and analytics

#### Scheduled Payments (Backend Implementation Complete)
The scheduled payments system is fully implemented at the database and model level with comprehensive features:

**Models and Database**:
- Complete database schema with migrations
- Eloquent models with relationships and business logic
- Factories and seeders with realistic Spanish data
- Comprehensive test coverage

**Available Features**:
- Recurring payment scheduling (daily, weekly, monthly, yearly)
- Debt management with installment tracking
- One-time scheduled payments
- Payment history with detailed status tracking
- Flexible metadata storage for payment details

**Status**: Backend implementation complete, API endpoints pending

**Usage Example**:
```php
// Create a recurring subscription payment
$payment = ScheduledPayment::create([
    'name' => 'Netflix Premium',
    'payment_type' => PaymentType::Recurring,
    'status' => PaymentStatus::Active,
    'amount' => 35.90,
    'user_id' => $user->id,
    'account_id' => $account->id,
    'category_id' => $category->id
]);

// Set up monthly recurring schedule
$payment->paymentSchedule()->create([
    'frequency' => PaymentFrequency::Monthly,
    'interval' => 1,
    'auto_process' => true
]);
```

## Implementation Status

### ✅ Fully Implemented Features

#### Core Financial Management
- ✅ **User Authentication**: Complete auth system with Laravel Sanctum
- ✅ **Multi-Currency Support**: Full currency management with 8+ pre-configured currencies
- ✅ **Account Management**: CRUD operations, balance tracking, account types
- ✅ **Category Management**: Hierarchical categories with parent/subcategory support
- ✅ **Transaction Management**: Income, expense, and transfer tracking with full CRUD

#### Advanced Features
- ✅ **Dashboard Analytics**: Comprehensive financial reporting and insights
- ✅ **Data Export**: PDF and CSV export for accounts and transactions
- ✅ **Monthly Reports**: Detailed financial analysis with trends and comparisons
- ✅ **Quick Statistics**: Real-time financial metrics and summaries

#### Scheduled Payments System (Backend Complete)
- ✅ **Database Schema**: 4 tables with complete relationships
- ✅ **Eloquent Models**: Full business logic and relationships
- ✅ **Payment Types**: Recurring, debt management, one-time payments
- ✅ **Status Management**: Active, paused, completed, cancelled, overdue states
- ✅ **History Tracking**: Complete payment execution history
- ✅ **Test Coverage**: Comprehensive factories, seeders, and test files

### 🚧 Pending Implementation

#### API Endpoints for Scheduled Payments
- ⏳ **ScheduledPayment Controller**: CRUD operations for scheduled payments
- ⏳ **PaymentSchedule Management**: API for payment frequency configuration
- ⏳ **Debt Management API**: Endpoints for debt tracking and installment management
- ⏳ **Payment History API**: Historical payment data and analytics
- ⏳ **Payment Processing**: Automated payment execution and transaction creation

#### Advanced Scheduled Payment Features
- ⏳ **Notification System**: Payment reminders and due date alerts
- ⏳ **Payment Templates**: Reusable payment configurations
- ⏳ **Batch Processing**: Bulk payment operations
- ⏳ **Calendar Integration**: Payment scheduling with calendar views

### 📁 Project Structure

The codebase is well-organized with clear separation of concerns:

```
app/
├── Enums/               # Payment types, statuses, and frequencies
├── Http/
│   ├── Controllers/     # API controllers for core features
│   ├── Resources/       # API response formatting
│   └── Requests/        # Form validation
├── Models/             # Eloquent models with relationships
├── Services/           # Business logic and data processing
└── Policies/           # Authorization rules

database/
├── migrations/         # Database schema including scheduled payments
├── factories/          # Model factories for testing
└── seeders/           # Sample data generation

tests/
├── Feature/           # Integration tests
└── Unit/              # Unit tests for services and models
```

This wallet application provides a solid foundation for personal finance management with room for future enhancements in the scheduled payments system.
