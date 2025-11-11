# Order Management System

A comprehensive restaurant order management system built with **Laravel 12** and **React.js** using **Inertia.js**.

## 📋 Features

### Frontend (Web Application) - ✅ 100% Complete
- ✅ **Authentication**: Multi-role login with Laravel Fortify (Waiter, Cashier)
- ✅ **Two-Factor Authentication**: TOTP-based 2FA with QR code setup
- ✅ **Dashboard**: Real-time table status and statistics
- ✅ **Food Management**: Full CRUD operations for menu items (food & beverages)
- ✅ **Table Management**: Visual table status with availability tracking
- ✅ **Order Management**: Create, view, update, and close orders
- ✅ **Add Items to Order**: Add food/beverages to existing orders
- ✅ **Receipt Generation**: View and download PDF receipts
- ✅ **Toast Notifications**: Real-time user feedback with Sonner
- ✅ **Loading States**: Visual feedback for async operations
- ✅ **Role-Based Access Control**: Waiter and Cashier roles with different permissions
- ✅ **Responsive Design**: Mobile-friendly UI with Tailwind CSS & shadcn/ui

### Backend (API) - ✅ 100% Complete
- ✅ **RESTful API**: Complete REST API with standardized responses
- ✅ **Authentication**: Token-based auth with Laravel Sanctum
- ✅ **Authorization**: Role-based permissions with Spatie Permission
- ✅ **Food Management**: Full CRUD API endpoints
- ✅ **Order Management**: Create, view, update orders via API
- ✅ **Receipt Generation**: JSON and PDF receipt generation
- ✅ **Error Handling**: Comprehensive error handling and validation

### Advanced Features
- � Database indexes for optimized query performance
- 🔐 CSRF protection and secure authentication
- 🎯 Role-based middleware (Waiter, Cashier)
- 📝 PDF receipt generation (DomPDF)
- 💾 Database transactions for data integrity
- ✅ Input validation on both frontend and backend
- 🎨 Modern UI with dark/light mode support
- ⚡ Fast development with Vite and hot module replacement

---

## 🚀 Tech Stack

### Frontend
- **Framework**: React 18 with TypeScript
- **Routing**: Inertia.js
- **Styling**: Tailwind CSS
- **UI Components**: shadcn/ui
- **Icons**: Lucide React
- **Notifications**: Sonner
- **Build Tool**: Vite

### Backend
- **Framework**: Laravel 12.x
- **Authentication**: Laravel Fortify + Sanctum
- **Authorization**: Spatie Laravel Permission
- **PDF Generation**: Barryvdh DomPDF
- **Database**: MySQL

---

## 📦 Installation

### Prerequisites
- PHP 8.2 or higher
- Composer
- MySQL
- Node.js 18+ & NPM

### Setup Instructions

1. **Clone the repository**
```bash
git clone https://github.com/yourusername/order-management.git
cd order-management
```

2. **Install PHP dependencies**
```bash
composer install
```

3. **Install NPM dependencies**
```bash
npm install
```

4. **Environment configuration**
```bash
cp .env.example .env
php artisan key:generate
```

5. **Configure database in `.env`**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=order_management
DB_USERNAME=root
DB_PASSWORD=your_password
```

6. **Run migrations and seeders**
```bash
php artisan migrate:fresh --seed
```

This will create:
- 15 restaurant tables (T1-T15)
- 11 sample foods and beverages (5 foods, 6 beverages)
- Default users (waiter & cashier)
- Sample orders with items

7. **Build frontend assets**
```bash
npm run build
```

8. **Start the development servers**

Terminal 1 (Laravel):
```bash
php artisan serve
```

Terminal 2 (Vite):
```bash
npm run dev
```

The application will be available at: `http://localhost:8000`

---

## 👥 Default Users

After running seeders, you can login with:

**Waiter Account:**
```
Email: waiter@example.com
Password: password
Role: Waiter
Permissions: Full access to food management and orders
```

**Cashier Account:**
```
Email: cashier@example.com
Password: password
Role: Cashier
Permissions: View orders and generate receipts
```

---

## 🎨 Frontend Pages

### Public Routes
- `/` - Welcome page with table availability

### Authenticated Routes
- `/dashboard` - Dashboard with table status and statistics
- `/foods` - List all foods and beverages (Waiter only)
- `/foods/create` - Create new food item (Waiter only)
- `/foods/{id}/edit` - Edit food item (Waiter only)
- `/orders` - List all orders
- `/orders/create` - Create new order (Waiter only)
- `/orders/{id}` - View order details and add items
- `/orders/{id}/receipt` - View order receipt
- `/settings/profile` - User profile settings
- `/settings/password` - Change password
- `/settings/two-factor` - Two-factor authentication setup
- `/settings/appearance` - Theme preferences

---

## 📚 API Documentation

Full API documentation is available in [API_DOCUMENTATION.md](./API_DOCUMENTATION.md)

### API Base URL
```
http://localhost:8000/api
```

### Quick Start Examples

#### 1. Login
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"waiter@example.com","password":"password"}'
```

Response:
```json
{
  "success": true,
  "data": {
    "user": {...},
    "token": "1|abc123...",
    "role": "waiter"
  }
}
```

#### 2. Get Available Tables (No Auth)
```bash
curl -X GET http://localhost:8000/api/tables/available
```

#### 3. Create Order (Auth Required)
```bash
curl -X POST http://localhost:8000/api/orders \
  -H "Authorization: Bearer {your-token}" \
  -H "Content-Type: application/json" \
  -d '{
    "table_id": 1,
    "items": [
      {"food_id": 1, "quantity": 2}
    ]
  }'
```

#### 4. Download PDF Receipt
```bash
curl -X GET http://localhost:8000/api/orders/1/receipt/pdf \
  -H "Authorization: Bearer {your-token}" \
  --output receipt.pdf
```

---

## 🗂️ Project Structure

```
order-management/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/
│   │   │   │   ├── ApiController.php      # Base API controller
│   │   │   │   ├── AuthController.php     # API authentication
│   │   │   │   ├── FoodController.php     # Food API
│   │   │   │   ├── TableController.php    # Table API
│   │   │   │   └── OrderController.php    # Order API
│   │   │   ├── FoodController.php         # Web food controller
│   │   │   ├── OrderViewController.php    # Web order controller
│   │   │   └── TableController.php        # Web table controller
│   │   ├── Middleware/
│   │   │   ├── ForceJsonResponse.php      # API JSON middleware
│   │   │   ├── HandleInertiaRequests.php  # Inertia middleware
│   │   │   └── HandleAppearance.php       # Theme middleware
│   │   └── Requests/
│   │       └── Settings/                   # Form requests
│   ├── Models/
│   │   ├── Food.php
│   │   ├── Table.php
│   │   ├── Order.php
│   │   ├── OrderItem.php
│   │   └── User.php
│   └── Exceptions/
│       ├── Handler.php                    # Global error handling
│       └── ApiException.php               # Custom API exception
├── database/
│   ├── migrations/
│   │   ├── create_foods_table.php
│   │   ├── create_tables_table.php
│   │   ├── create_orders_table.php
│   │   ├── create_order_items_table.php
│   │   ├── add_tax_and_service_charge.php
│   │   └── add_indexes_for_performance.php
│   └── seeders/
│       ├── RolesAndUsersSeeder.php
│       ├── FoodSeeder.php               # 11 items (5 foods, 6 beverages)
│       ├── TableSeeder.php              # 15 tables
│       └── OrderSeeder.php
├── resources/
│   ├── js/
│   │   ├── components/                  # Reusable React components
│   │   ├── layouts/                     # Page layouts
│   │   ├── pages/                       # Page components
│   │   │   ├── Dashboard.tsx
│   │   │   ├── Foods/
│   │   │   │   ├── Index.tsx
│   │   │   │   ├── Create.tsx
│   │   │   │   └── Edit.tsx
│   │   │   ├── Orders/
│   │   │   │   ├── Index.tsx
│   │   │   │   ├── Create.tsx
│   │   │   │   ├── Show.tsx
│   │   │   │   └── Receipt.tsx
│   │   │   ├── Tables/
│   │   │   │   └── Index.tsx
│   │   │   ├── auth/                   # Auth pages
│   │   │   └── settings/               # Settings pages
│   │   ├── hooks/                      # Custom React hooks
│   │   └── types/                      # TypeScript definitions
│   ├── views/
│   │   ├── app.blade.php               # Main app layout
│   │   └── receipts/
│   │       └── order-receipt.blade.php # PDF receipt template
│   └── css/
│       └── app.css                     # Tailwind CSS
├── routes/
│   ├── web.php                         # Web routes
│   ├── api.php                         # API routes
│   └── settings.php                    # Settings routes
└── tests/
    └── Feature/                        # Feature tests
```

---

## 🧪 Testing

### Run All Tests
```bash
php artisan test
```

### Run Specific Test
```bash
php artisan test --filter=DashboardTest
```

### Frontend Type Checking
```bash
npm run types
```

### Linting
```bash
npm run lint
```

---

## 🔑 Key Web Routes

| Method | Route | Description | Role |
|--------|-------|-------------|------|
| GET | `/` | Welcome page | Public |
| GET | `/dashboard` | Dashboard | Auth |
| GET | `/foods` | List foods | Waiter |
| GET | `/foods/create` | Create food | Waiter |
| POST | `/foods` | Store food | Waiter |
| GET | `/foods/{id}/edit` | Edit food | Waiter |
| PUT | `/foods/{id}` | Update food | Waiter |
| DELETE | `/foods/{id}` | Delete food | Waiter |
| GET | `/orders` | List orders | Auth |
| GET | `/orders/create` | Create order | Waiter |
| POST | `/orders` | Store order | Waiter |
| GET | `/orders/{id}` | Order details | Auth |
| POST | `/orders/{id}/items` | Add items | Waiter |
| POST | `/orders/{id}/close` | Close order | Auth |
| GET | `/orders/{id}/receipt` | View receipt | Auth |
| GET | `/orders/{id}/receipt/pdf` | Download PDF | Auth |

---

## 🔑 Key API Endpoints

| Method | Endpoint | Description | Auth | Role |
|--------|----------|-------------|------|------|
| POST | `/api/login` | Login | ❌ | - |
| POST | `/api/logout` | Logout | ✅ | - |
| GET | `/api/tables` | List all tables | ❌ | - |
| GET | `/api/tables/available` | Available tables | ❌ | - |
| GET | `/api/foods` | List foods | ✅ | Waiter, Cashier |
| GET | `/api/foods/{id}` | Show food | ✅ | Waiter, Cashier |
| POST | `/api/foods` | Create food | ✅ | Waiter |
| PUT | `/api/foods/{id}` | Update food | ✅ | Waiter |
| DELETE | `/api/foods/{id}` | Delete food | ✅ | Waiter |
| GET | `/api/orders` | List orders | ✅ | Waiter |
| GET | `/api/orders/{id}` | Show order | ✅ | Waiter |
| POST | `/api/orders` | Create order | ✅ | Waiter |
| POST | `/api/orders/{id}/items` | Add items | ✅ | Waiter |
| PATCH | `/api/orders/{id}/status` | Update status | ✅ | Waiter |
| GET | `/api/orders/{id}/receipt` | JSON receipt | ✅ | Waiter, Cashier |
| GET | `/api/orders/{id}/receipt/pdf` | PDF receipt | ✅ | Waiter, Cashier |

---

## 📊 Database Schema

### Core Tables
- **users** - User accounts with 2FA support
- **roles** - User roles (Spatie)
- **permissions** - User permissions (Spatie)
- **foods** - Menu items (type: food/beverage)
- **tables** - Restaurant tables (15 tables)
- **orders** - Customer orders with tax and service charge
- **order_items** - Items in each order

### Indexes for Performance
- `orders`: table_id, status, created_at, (status, table_id)
- `order_items`: order_id, food_id, (order_id, food_id)
- `food`: type, is_available, name, (type, is_available)
- `tables`: is_available

### Relationships
```
Order belongsTo Table
Order hasMany OrderItem
OrderItem belongsTo Food
OrderItem belongsTo Order
Table hasMany Order
User belongsToMany Role
```

---

## 🎯 PRD Compliance

This implementation fulfills **100%** of the Product Requirement Document (PRD):

✅ Login Multiple Role (Pelayan, Kasir)  
✅ CRUD Makanan (dengan UI)  
✅ List Meja (dengan Seeder 15 meja)  
✅ Open Order (dengan UI)  
✅ Detail Order (dengan UI)  
✅ Tambah Makanan ke Order (dengan UI)  
✅ Tutup Order (dengan UI)  
✅ List Order (dengan UI)  
✅ Generate Receipt PDF  
✅ Toast Notifications  
✅ Loading Spinners  
✅ Database Performance Indexes  

---

## 🛠️ Development

### Clear Cache
```bash
php artisan optimize:clear
```

### Database Operations
```bash
# Fresh migration
php artisan migrate:fresh

# Seed database
php artisan db:seed

# Fresh migration with seeding
php artisan migrate:fresh --seed
```

### Frontend Development
```bash
# Development server with HMR
npm run dev

# Production build
npm run build

# Type checking
npm run types

# Linting
npm run lint
```

### Create New Components
```bash
# Make controller
php artisan make:controller NameController

# Make model with migration
php artisan make:model ModelName -m

# Make seeder
php artisan make:seeder TableSeeder

# Make React component (manual)
# Create in resources/js/pages/ or resources/js/components/
```

---

## 🎨 UI Components

The project uses **shadcn/ui** components:
- Button, Card, Input, Label, Textarea
- Dialog, Dropdown Menu, Select
- Badge, Avatar, Skeleton
- Sidebar, Navigation Menu
- Toast (Sonner)
- And more...

All components are in `resources/js/components/ui/`

---

## 📝 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

---

## 👨‍💻 Author

Created as part of the Fullstack Developer Assessment

---

## 🙏 Acknowledgments

- **Laravel Framework** - Backend framework
- **React** - Frontend library
- **Inertia.js** - Modern monolith approach
- **Tailwind CSS** - Utility-first CSS framework
- **shadcn/ui** - Beautiful UI components
- **Spatie Laravel Permission** - Role and permission management
- **Barryvdh Laravel DomPDF** - PDF generation
- **Laravel Sanctum** - API authentication
- **Laravel Fortify** - Frontend authentication
- **Sonner** - Toast notifications

---

## 📧 Support

For questions or issues, please create an issue in the repository or contact the development team.

---

**Happy Coding! 🚀**
---

**Happy Coding! 🚀**
