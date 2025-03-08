# CAFM System Development Tasks (Laravel Based)

## Progress Tracking
- ✅ = Completed
- 🟡 = In Progress
- ⭕ = Not Started
- ❌ = Blocked
- 🔄 = Needs Review

## Phase 0: Project Setup
### Environment Setup ⭕
- [ ] Install Required Software
  - [ ] PHP 8.1+
  - [ ] Composer 2.0+
  - [ ] MySQL 8.0+
  - [ ] Node.js & NPM
  - [ ] Git

- [ ] Laravel Installation
  - [ ] Create new Laravel project: `composer create-project laravel/laravel laravel-cafm`
  - [ ] Set up Git repository
  - [ ] Configure .env file
  - [ ] Generate application key
  - [ ] Setup MySQL database

- [ ] Install Core Packages
  - [ ] Authentication: `composer require laravel/breeze --dev`
  - [ ] API Authentication: `composer require laravel/sanctum`
  - [ ] Permissions: `composer require spatie/laravel-permission`
  - [ ] Excel Handling: `composer require maatwebsite/excel`
  - [ ] Image Handling: `composer require intervention/image`
  - [ ] QR Code: `composer require simplesoftwareio/simple-qrcode`
  - [ ] PDF Generation: `composer require barryvdh/laravel-snappy`
  - [ ] IDE Helper: `composer require --dev barryvdh/laravel-ide-helper`
  - [ ] Debug Bar: `composer require --dev barryvdh/laravel-debugbar`

- [ ] Frontend Setup
  - [ ] Install Node dependencies: `npm install`
  - [ ] Install Tailwind CSS
  - [ ] Configure Vite
  - [ ] Setup basic layouts

### Project Structure Setup ⭕
```
laravel-cafm/
├── app/
│   ├── Console/           # Custom commands
│   ├── Http/
│   │   ├── Controllers/   # Controllers by module
│   │   │   ├── Auth/
│   │   │   ├── Admin/
│   │   │   ├── Building/
│   │   │   ├── Asset/
│   │   │   └── WorkOrder/
│   │   ├── Middleware/    # Custom middleware
│   │   ├── Requests/      # Form requests by module
│   │   └── Resources/     # API resources
│   ├── Models/            # Eloquent models
│   ├── Services/          # Business logic services
│   ├── Repositories/      # Repository pattern classes
│   ├── Events/           # Event classes
│   ├── Listeners/        # Event listeners
│   └── Providers/        # Service providers
├── config/               # Configuration files
├── database/
│   ├── migrations/       # Database migrations
│   ├── factories/        # Model factories
│   └── seeders/         # Database seeders
├── resources/
│   ├── views/           
│   │   ├── layouts/     # Base layouts
│   │   ├── components/  # Blade components
│   │   └── modules/     # Module-specific views
│   ├── js/             
│   │   ├── components/  # Vue/React components
│   │   └── utils/       # JavaScript utilities
│   └── css/            
│       └── modules/     # Module-specific styles
├── routes/
│   ├── web.php         # Web routes
│   ├── api.php         # API routes
│   └── channels.php    # Broadcasting channels
└── tests/              # Test files by module
```

## Phase 1: Database Design and Migrations

### Database Migrations ⭕
#### User Management Tables
- [ ] Create migrations for:
  ```php
  // users table (extends Laravel default)
  Schema::table('users', function (Blueprint $table) {
      $table->string('status')->default('active');
      $table->string('employee_id')->nullable()->unique();
      $table->string('department')->nullable();
      $table->string('position')->nullable();
      $table->string('phone')->nullable();
      $table->timestamp('last_login')->nullable();
      $table->json('preferences')->nullable();
      $table->softDeletes();
  });

  // roles and permissions (via spatie/laravel-permission)
  php artisan permission:create-permission-tables

  // user_sessions
  Schema::create('user_sessions', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained()->onDelete('cascade');
      $table->string('token');
      $table->string('device_info')->nullable();
      $table->ipAddress('ip_address')->nullable();
      $table->timestamp('expires_at');
      $table->timestamps();
  });

  // activity_logs
  Schema::create('activity_logs', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->nullable()->constrained();
      $table->string('action');
      $table->string('module');
      $table->morphs('loggable');
      $table->json('old_values')->nullable();
      $table->json('new_values')->nullable();
      $table->ipAddress('ip_address')->nullable();
      $table->string('user_agent')->nullable();
      $table->timestamps();
  });
  ```

#### Building Management Tables
- [ ] Create migrations for:
  ```php
  // buildings
  Schema::create('buildings', function (Blueprint $table) {
      $table->id();
      $table->string('name');
      $table->string('code')->unique();
      $table->text('description')->nullable();
      $table->string('address');
      $table->string('city');
      $table->string('state');
      $table->string('country');
      $table->string('postal_code');
      $table->decimal('gis_latitude', 10, 8)->nullable();
      $table->decimal('gis_longitude', 11, 8)->nullable();
      $table->string('status')->default('active');
      $table->json('metadata')->nullable();
      $table->timestamps();
      $table->softDeletes();
  });

  // floors
  Schema::create('floors', function (Blueprint $table) {
      $table->id();
      $table->foreignId('building_id')->constrained()->onDelete('cascade');
      $table->string('name');
      $table->string('floor_number');
      $table->decimal('area', 10, 2)->nullable();
      $table->string('floor_plan_url')->nullable();
      $table->string('status')->default('active');
      $table->json('metadata')->nullable();
      $table->timestamps();
      $table->softDeletes();
  });

  // spaces
  Schema::create('spaces', function (Blueprint $table) {
      $table->id();
      $table->foreignId('floor_id')->constrained()->onDelete('cascade');
      $table->string('name');
      $table->string('code')->unique();
      $table->string('type');
      $table->decimal('area', 10, 2)->nullable();
      $table->integer('capacity')->nullable();
      $table->string('status')->default('active');
      $table->json('metadata')->nullable();
      $table->timestamps();
      $table->softDeletes();
  });
  ```

#### Asset Management Tables
- [ ] Create migrations for:
  ```php
  // asset_categories
  Schema::create('asset_categories', function (Blueprint $table) {
      $table->id();
      $table->string('name');
      $table->string('code')->unique();
      $table->text('description')->nullable();
      $table->nestedSet(); // For hierarchical categories
      $table->timestamps();
  });

  // assets
  Schema::create('assets', function (Blueprint $table) {
      $table->id();
      $table->string('name');
      $table->string('asset_number')->unique();
      $table->foreignId('category_id')->constrained('asset_categories');
      $table->string('status')->default('active');
      $table->foreignId('space_id')->nullable()->constrained();
      $table->date('purchase_date')->nullable();
      $table->date('warranty_expiry')->nullable();
      $table->decimal('purchase_cost', 15, 2)->nullable();
      $table->string('manufacturer')->nullable();
      $table->string('model')->nullable();
      $table->string('serial_number')->nullable();
      $table->string('qr_code')->unique();
      $table->json('specifications')->nullable();
      $table->json('metadata')->nullable();
      $table->timestamps();
      $table->softDeletes();
  });

  // maintenance_schedules
  Schema::create('maintenance_schedules', function (Blueprint $table) {
      $table->id();
      $table->foreignId('asset_id')->constrained();
      $table->string('type');
      $table->string('frequency');
      $table->json('schedule_config');
      $table->date('last_maintenance')->nullable();
      $table->date('next_maintenance');
      $table->timestamps();
  });

  // maintenance_logs
  Schema::create('maintenance_logs', function (Blueprint $table) {
      $table->id();
      $table->foreignId('asset_id')->constrained();
      $table->foreignId('schedule_id')->nullable()->constrained('maintenance_schedules');
      $table->foreignId('performed_by')->constrained('users');
      $table->string('type');
      $table->text('description');
      $table->string('status');
      $table->decimal('cost', 15, 2)->nullable();
      $table->json('parts_used')->nullable();
      $table->timestamps();
  });
  ```

#### Work Order Management Tables
- [ ] Create migrations for:
  ```php
  // work_orders
  Schema::create('work_orders', function (Blueprint $table) {
      $table->id();
      $table->string('number')->unique();
      $table->string('title');
      $table->text('description');
      $table->string('priority');
      $table->string('status');
      $table->foreignId('requested_by')->constrained('users');
      $table->foreignId('assigned_to')->nullable()->constrained('users');
      $table->foreignId('asset_id')->nullable()->constrained();
      $table->foreignId('space_id')->nullable()->constrained();
      $table->datetime('due_date')->nullable();
      $table->datetime('started_at')->nullable();
      $table->datetime('completed_at')->nullable();
      $table->json('metadata')->nullable();
      $table->timestamps();
      $table->softDeletes();
  });

  // work_order_comments
  Schema::create('work_order_comments', function (Blueprint $table) {
      $table->id();
      $table->foreignId('work_order_id')->constrained()->onDelete('cascade');
      $table->foreignId('user_id')->constrained();
      $table->text('comment');
      $table->timestamps();
  });

  // work_order_attachments
  Schema::create('work_order_attachments', function (Blueprint $table) {
      $table->id();
      $table->foreignId('work_order_id')->constrained()->onDelete('cascade');
      $table->string('file_name');
      $table->string('file_path');
      $table->string('file_type');
      $table->integer('file_size');
      $table->timestamps();
  });
  ```

### Models Implementation ⭕
- [ ] Create Base Model Class with Common Traits
- [ ] User Management Models:
  - [ ] User (extend Laravel User)
  - [ ] Role (Spatie Permission)
  - [ ] Permission (Spatie Permission)
  - [ ] ActivityLog

- [ ] Building Management Models:
  - [ ] Building
  - [ ] Floor
  - [ ] Space

- [ ] Asset Management Models:
  - [ ] AssetCategory
  - [ ] Asset
  - [ ] MaintenanceSchedule
  - [ ] MaintenanceLog

- [ ] Work Order Models:
  - [ ] WorkOrder
  - [ ] WorkOrderComment
  - [ ] WorkOrderAttachment

### Repository Layer ⭕
- [ ] Create Base Repository Interface and Class
- [ ] Implement Repositories for each Model
- [ ] Add Caching Layer

### Service Layer ⭕
- [ ] Create Base Service Class
- [ ] Implement Services for Complex Business Logic
- [ ] Add Event Dispatching

### Database Seeders ⭕
- [ ] Create Base Seeder Class
- [ ] Create Seeders for:
  - [ ] Roles and Permissions
  - [ ] Admin User
  - [ ] Asset Categories
  - [ ] Test Buildings and Spaces
  - [ ] Sample Assets
  - [ ] Test Work Orders

## Phase 2: Core Features Implementation

### Authentication System ⭕
- [ ] Install and Configure Laravel Breeze
  - [ ] Customize Login/Register Views
  - [ ] Add Employee ID Login Option
  - [ ] Implement Remember Me
  - [ ] Add Login Throttling

- [ ] Implement 2FA
  - [ ] Setup Google Authenticator
  - [ ] Add Backup Codes
  - [ ] Recovery Process

- [ ] Email Verification
  - [ ] Customize Verification Emails
  - [ ] Add Verification Reminder

- [ ] Password Management
  - [ ] Strong Password Policy
  - [ ] Password Reset Flow
  - [ ] Password Expiry

### API Development ⭕
- [ ] Setup Laravel Sanctum
  - [ ] Configure CORS
  - [ ] Token Management
  - [ ] Rate Limiting

- [ ] Create API Resources
  - [ ] Transform Models to JSON
  - [ ] Include Relationships
  - [ ] Version Control

- [ ] Implement API Endpoints
  - [ ] Authentication Routes
  - [ ] CRUD Operations
  - [ ] Bulk Operations
  - [ ] File Uploads

- [ ] API Documentation
  - [ ] Install Laravel Scribe
  - [ ] Document Endpoints
  - [ ] Generate API Docs

### Core Module Development ⭕

#### Building Management
- [ ] CRUD Operations
  - [ ] Building Management
  - [ ] Floor Management
  - [ ] Space Management

- [ ] File Handling
  - [ ] Floor Plan Upload
  - [ ] Image Processing
  - [ ] Storage Configuration

- [ ] Space Assignment
  - [ ] Capacity Management
  - [ ] Occupancy Tracking
  - [ ] Conflict Resolution

#### Asset Management
- [ ] Asset Registration
  - [ ] Basic Information
  - [ ] Category Management
  - [ ] Location Assignment

- [ ] QR Code System
  - [ ] Code Generation
  - [ ] Label Printing
  - [ ] Mobile Scanning

- [ ] Maintenance
  - [ ] Schedule Creation
  - [ ] Notification System
  - [ ] History Tracking

#### Work Order System
- [ ] Work Order Processing
  - [ ] Creation Flow
  - [ ] Assignment Rules
  - [ ] Priority Management

- [ ] Status Management
  - [ ] State Machine
  - [ ] Transitions
  - [ ] Notifications

- [ ] File Attachments
  - [ ] Upload System
  - [ ] Preview Generation
  - [ ] Storage Management

## Phase 3: Frontend Development

### Laravel Blade Components ⭕
- [ ] Layout Components
  - [ ] Master Layout
  - [ ] Navigation
  - [ ] Sidebar
  - [ ] Footer

- [ ] Form Components
  - [ ] Input Fields
  - [ ] Select Menus
  - [ ] File Upload
  - [ ] Date Picker

- [ ] UI Components
  - [ ] Data Tables
  - [ ] Cards
  - [ ] Modals
  - [ ] Alerts

### JavaScript Integration ⭕
- [ ] Setup Vue.js/Alpine.js
- [ ] Component Development
- [ ] State Management
- [ ] API Integration

### Asset Management ⭕
- [ ] Configure Vite
- [ ] SCSS Architecture
- [ ] JavaScript Bundling
- [ ] Image Optimization

## Phase 4: Advanced Features

### Reporting System ⭕
- [ ] Report Builder
  - [ ] Template System
  - [ ] Parameter Management
  - [ ] Export Options

- [ ] Scheduled Reports
  - [ ] Configuration
  - [ ] Distribution
  - [ ] Archive System

### Integration Features ⭕
- [ ] Email System
  - [ ] Template System
  - [ ] Queue Configuration
  - [ ] Delivery Tracking

- [ ] Notification System
  - [ ] Multiple Channels
  - [ ] Template Management
  - [ ] Preference Settings

## Phase 5: Testing

### Unit Testing ⭕
- [ ] Model Tests
- [ ] Repository Tests
- [ ] Service Tests
- [ ] Helper Tests

### Feature Testing ⭕
- [ ] Controller Tests
- [ ] API Tests
- [ ] Integration Tests
- [ ] Browser Tests

### Performance Testing ⭕
- [ ] Load Testing
- [ ] Database Query Testing
- [ ] Cache Testing
- [ ] API Response Testing

## Phase 6: Deployment and DevOps

### Server Setup ⭕
- [ ] Environment Configuration
- [ ] Web Server Setup
- [ ] Database Optimization
- [ ] Cache Configuration

### Monitoring ⭕
- [ ] Error Tracking
- [ ] Performance Monitoring
- [ ] Security Monitoring
- [ ] Backup System

## Phase 7: AI Integration

### Basic Features ⭕
- [ ] Predictive Maintenance
- [ ] Work Order Assignment
- [ ] Resource Optimization
- [ ] Pattern Detection

### Advanced Features ⭕
- [ ] Energy Management
- [ ] Space Optimization
- [ ] Predictive Analytics
- [ ] Anomaly Detection

## Dependencies
- PHP 8.1+
- Composer 2.0+
- MySQL 8.0+
- Node.js & NPM
- Required Laravel Packages:
  - laravel/framework: ^10.0
  - laravel/breeze: ^1.20
  - laravel/sanctum: ^3.2
  - spatie/laravel-permission: ^5.10
  - maatwebsite/excel: ^3.1
  - simplesoftwareio/simple-qrcode: ^4.2
  - intervention/image: ^2.7
  - barryvdh/laravel-snappy: ^1.0
  - predis/predis: ^2.0

## Notes
- Follow Laravel best practices and conventions
- Implement repository pattern for data abstraction
- Use service layer for business logic
- Follow PSR-12 coding standards
- Implement proper logging and error handling
- Use Laravel's built-in security features
- Regular database backups
- Use Laravel Horizon for queue monitoring
- Document all major components and APIs
- Write comprehensive tests
- Use Git flow for version control 
