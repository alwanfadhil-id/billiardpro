# Business Requirement Document (BRD) - BilliardPro

## 1. Project Overview

### 1.1 Project Name
BilliardPro - Billing System for Billiard Business

### 1.2 Project Purpose
BilliardPro is a comprehensive billing and management system designed specifically for billiard halls. The system automates table usage tracking, calculates hourly charges, manages additional product sales, and provides comprehensive reporting for business analysis.

### 1.3 Project Scope
- Real-time table status monitoring
- Automated billing calculation (rounded up to the nearest hour)
- Additional product sales integration (drinks, snacks)
- Cash payment processing and receipt generation
- Daily, monthly, and yearly sales reporting
- User role management (admin/cashier)
- Inventory tracking and management
- Table differentiation (biasa, premium, vip)
- Dark mode UI for enhanced usability

### 1.4 Target Users
- Admin/Owner: Access to all features, reporting, configuration
- Cashier: Table management, transaction processing, payment handling

## 2. Business Requirements

### 2.1 Functional Requirements

#### 2.1.1 Table Management
- Display all tables with color-coded status (available, occupied, maintenance)
- Start new billing session on available tables
- Calculate duration and cost automatically
- End sessions and process payments
- Support for table maintenance status
- Support for different table types (biasa, premium, vip) with different rates

#### 2.1.2 Billing System
- Hourly rate configuration per table
- Automatic time calculation rounded up to nearest hour
- Additional item sales (food/drink) integration
- Payment processing with cash handling
- Receipt generation
- Accurate duration calculation with fallback for edge cases

#### 2.1.3 User Management
- Role-based access control (Admin, Cashier)
- Secure authentication system
- User profile management

#### 2.1.4 Reporting
- Daily sales reports
- Monthly sales reports
- Yearly sales reports
- Table usage statistics
- Revenue analysis
- Transaction history

#### 2.1.5 Product Management
- Manage products (drinks, snacks)
- Inventory tracking with stock levels
- Pricing management
- Low stock alerts

#### 2.1.6 Inventory Management
- Track stock levels for all products
- Automatic stock reduction when items are sold
- Stock movement history tracking
- Low stock notifications

### 2.2 Non-Functional Requirements

#### 2.2.1 Performance
- System should handle real-time updates with minimal latency
- Response time under 2 seconds for all operations
- Support concurrent users (up to 5 simultaneous cashiers)

#### 2.2.2 Usability
- Intuitive visual interface with large buttons
- Color-coded table grid for easy identification
- Mobile-responsive design for tablet use
- Dark mode support for reduced eye strain

#### 2.2.3 Security
- Secure user authentication
- Role-based access controls
- Data encryption for sensitive information

#### 2.2.4 Reliability
- System should maintain 99% uptime during business hours
- Automatic backup of transaction data
- Transaction integrity and audit trail
- Accurate duration calculation even in edge cases

## 3. System Specifications

### 3.1 Technical Architecture
- **Backend Framework**: Laravel 11
- **Frontend**: Livewire + Tailwind CSS + DaisyUI
- **Database**: MySQL/MariaDB/SQLite
- **Authentication**: Laravel Breeze
- **UI Components**: DaisyUI with dark mode

### 3.2 Database Schema Overview
- `users`: User accounts and roles
- `tables`: Billiard table information and rates
- `products`: Additional items (drinks/snacks) with inventory tracking
- `inventory_transactions`: Track stock movements
- `transactions`: Billing session records
- `transaction_items`: Additional item sales

### 3.3 Hardware Requirements
- Tablet or computer with minimum 768px screen width
- Thermal printer (optional) for receipts
- Reliable internet connection (for web-based system)

## 4. User Interface Requirements

### 4.1 Main Dashboard
- Visual grid of all tables with color-coded status
- Quick access to management functions
- Real-time billing calculation display
- Notification system for important events

### 4.2 Table Management
- Large, touch-friendly buttons
- Clear status indicators
- Quick start/end session controls
- Time and cost display for occupied tables

### 4.3 Payment Processing
- Simple payment flow
- Cash calculation helper
- Receipt preview and printing options
- Payment confirmation

### 4.4 Inventory Management
- Real-time stock level display
- Low stock alerts
- Inventory transaction history
- Stock management interface

## 5. Business Rules

### 5.1 Billing Rules
- Time is calculated from session start to end
- Duration is rounded up to the next full hour
- Table rates can be configured individually
- Additional items are charged separately
- Use fallback calculation for duration if primary calculation fails

### 5.2 Session Rules
- Only available tables can start new sessions
- Maintenance tables cannot be booked
- Sessions must be properly ended to allow new bookings
- Unfinished sessions are tracked but not allowed to extend indefinitely

### 5.3 Payment Rules
- Exact change capability for cash payments
- Option to add tips
- Receipt generation upon payment completion
- Transaction history maintained permanently

### 5.4 Inventory Rules
- Products must have sufficient stock before they can be sold
- Stock levels are automatically reduced when items are sold in transactions
- Inventory transactions are recorded to track all stock movements
- Low stock alerts are generated when stock falls below minimum levels

## 6. Integration Requirements

### 6.1 Thermal Printing
- Support for ESC/POS thermal printers
- Fallback to browser printing if thermal printer unavailable

### 6.2 Backup and Export
- Database backup functionality
- Export reports to various formats (PDF, CSV)
- Data integrity checks

### 6.1 External Systems
- Thermal printer support (ESC/POS)
- Potential integration with inventory management
- Backup and synchronization capabilities

### 6.2 Reporting Systems
- Export capabilities for financial software
- API endpoints for future integrations
- Data export in standard formats (CSV, PDF)

## 7. Success Criteria

### 7.1 Functional Success Metrics
- All billing calculations are accurate
- Table management is intuitive and fast
- User authentication works reliably
- Reports are generated correctly

### 7.2 Usability Success Metrics
- New users can operate the system within 30 minutes of training
- Average transaction processing time is under 2 minutes
- Error rate is less than 1% of total transactions

### 7.3 Technical Success Metrics
- System uptime of 99% during operational hours
- Page load times under 3 seconds
- Data accuracy maintained at 100%

## 8. Constraints and Assumptions

### 8.1 Constraints
- System must work with existing hardware where possible
- Budget limitations for additional equipment
- Timeline for implementation
- Need for local data storage capability

### 8.2 Assumptions
- Staff will receive basic training on the system
- Internet connectivity is available but not required for core operations
- Hardware will be maintained and updated as needed
- Business operations follow standard billing practices

## 9. Risks and Mitigation

### 9.1 Technical Risks
- System downtime during peak hours: Implement backup systems
- Data loss: Regular automated backups
- Performance issues: Proper server configuration

### 9.2 Business Risks
- Staff resistance to new system: Adequate training and support
- Incorrect billing: Thorough testing and validation
- Hardware failure: Backup procedures and equipment plan

## 10. Project Timeline and Milestones

### Phase 1: Setup and Configuration (Week 1-2)
- Install Laravel 11 with Breeze
- Configure database and user authentication
- Set up basic UI with DaisyUI and dark mode

### Phase 2: Core Features (Week 3-4)
- Implement table management system
- Create billing calculation logic
- Develop payment processing flow

### Phase 3: Advanced Features (Week 5-6)
- Add reporting functionality
- Implement additional product sales
- Create admin management features

### Phase 4: Testing and Deployment (Week 7-8)
- Thorough testing including edge cases
- Staff training materials
- Go-live and support

## 11. Acceptance Criteria

- System allows starting and ending table sessions
- Billing calculations are accurate and properly rounded
- Payment processing works for cash transactions
- All user roles can access appropriate features
- Reports can be generated and viewed
- System handles concurrent users without errors
- UI is responsive and works on tablet devices
- Dark mode is properly implemented and functional