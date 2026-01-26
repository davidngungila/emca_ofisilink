# Android App Menu Overview

## Complete Menu Structure for EMCA OfficeLink Android Application

This document provides a comprehensive overview of all menus and navigation items to be implemented in the Android mobile application, based on the web application's sidebar structure and available mobile API endpoints.

---

## Main Navigation Menu Structure

### 1. **Dashboard** 🏠
- **Icon**: Home/Dashboard icon
- **Route**: `/mobile/v1/dashboard`
- **Access**: All authenticated users
- **Sub-items**:
  - Dashboard Overview
  - Statistics (`/dashboard/stats`)
  - Notifications (`/dashboard/notifications`)

---

### 2. **Petty Cash Management** 💰
- **Icon**: Money/Cash icon
- **Route**: `/mobile/v1/finance/petty-cash`
- **Access**: All authenticated users
- **Features**:
  - View petty cash requests
  - Create new petty cash request
  - View petty cash statistics
  - Approve/Reject requests (based on role)

---

### 3. **Imprest Management** 💳
- **Icon**: Credit Card icon
- **Route**: `/mobile/v1/finance/imprest`
- **Access**: All authenticated users
- **Features**:
  - View imprest requests
  - Create new imprest request
  - Submit receipts
  - Approve/Reject requests (based on role)

---

### 4. **Refund Requests** 🔄
- **Icon**: Refresh/Return icon
- **Route**: `/mobile/v1/finance/refunds` (if available)
- **Access**: All authenticated users
- **Features**:
  - View refund requests
  - Create new refund request
  - Track refund status

---

### 5. **Accounting Module** 💵
- **Icon**: Dollar/Circle icon
- **Route**: `/mobile/v1/finance/accounting` (if available)
- **Access**: Accountant, System Admin only
- **Features**:
  - Financial reports
  - Accounting dashboard
  - Transaction management

---

### 6. **File Management** 📁
- **Icon**: Folder icon
- **Access**: All authenticated users
- **Sub-menus**:

#### 6.1. **Digital Files**
- **Route**: `/mobile/v1/files/digital`
- **Features**:
  - Browse digital files
  - View folders (`/files/digital/folders`)
  - Upload files (`/files/digital/upload`)
  - Download files (`/files/digital/{id}/download`)
  - Search files (`/files/digital/search`)
  - Request file access
  - View my access requests
  - Approve/Reject access requests (based on role)

#### 6.2. **My Documents**
- **Route**: `/mobile/v1/files/digital/my-documents` (if available)
- **Features**:
  - Personal document library
  - Quick access to user's documents

#### 6.3. **Physical Racks**
- **Route**: `/mobile/v1/files/physical`
- **Features**:
  - Browse physical file racks
  - View categories (`/files/physical/categories`)
  - Request physical files (`/files/physical/{id}/request`)
  - View my physical file requests
  - Approve/Reject requests (based on role)
  - Return physical files

---

### 7. **HR Management** 👔
- **Icon**: Briefcase icon
- **Access**: All authenticated users (with role-based sub-menu access)
- **Sub-menus**:

#### 7.1. **Leave Management**
- **Route**: `/mobile/v1/leaves`
- **Features**:
  - View all leaves (`/leaves`)
  - My leaves (`/leaves/my`)
  - Pending leaves (`/leaves/pending`)
  - Create leave request (`POST /leaves`)
  - View leave balance (`/leaves/balance`)
  - View leave types (`/leaves/types`)
  - Approve/Reject leaves (based on role)
  - Cancel leave request

#### 7.2. **Employee Directory**
- **Route**: `/mobile/v1/hr/employees`
- **Features**:
  - View employee list
  - View employee details
  - Search employees

#### 7.3. **Attendance**
- **Route**: `/mobile/v1/attendance`
- **Features**:
  - View attendance records (`/attendance`)
  - My attendance (`/attendance/my`)
  - Daily attendance (`/attendance/daily/{date}`)
  - Attendance summary (`/attendance/summary`)
  - Check-in (`POST /attendance/check-in`)
  - Check-out (`POST /attendance/check-out`)

#### 7.4. **Assessments**
- **Route**: `/mobile/v1/hr/assessments`
- **Features**:
  - View assessments (`/hr/assessments`)
  - My assessments (`/hr/assessments/my`)
  - Create assessment (`POST /hr/assessments`)
  - Submit progress updates
  - View progress history

#### 7.5. **Sick Sheet**
- **Route**: `/mobile/v1/hr/sick-sheets`
- **Features**:
  - View sick sheets (`/hr/sick-sheets`)
  - My sick sheets (`/hr/sick-sheets/my`)
  - Submit sick sheet (`POST /hr/sick-sheets`)
  - Approve/Reject sick sheets (based on role)

#### 7.6. **Permissions**
- **Route**: `/mobile/v1/hr/permissions`
- **Features**:
  - View permission requests (`/hr/permissions`)
  - My permissions (`/hr/permissions/my`)
  - Create permission request (`POST /hr/permissions`)
  - Approve/Reject permissions (based on role)
  - Confirm return from permission

#### 7.7. **Notices**
- **Route**: `/mobile/v1/hr/notices` (if available)
- **Features**:
  - View company notices
  - Read notice details

#### 7.8. **Payroll**
- **Route**: `/mobile/v1/finance/payroll`
- **Features**:
  - View payroll records (`/finance/payroll`)
  - My payroll (`/finance/payroll/my`)
  - View payroll details

#### 7.9. **Departments** (HR & Admin only)
- **Route**: `/mobile/v1/departments`
- **Features**:
  - View departments
  - View department members
  - Department details

#### 7.10. **Positions** (HR & Admin only)
- **Route**: `/mobile/v1/hr/positions` (if available)
- **Features**:
  - View positions
  - Position management

#### 7.11. **Recruitment** (HR, HOD, CEO, Admin only)
- **Route**: `/mobile/v1/hr/jobs`
- **Features**:
  - View job postings (`/hr/jobs`)
  - Job details
  - Apply for jobs (`POST /hr/jobs/{id}/apply`)
  - View applications (for HR)

---

### 8. **Task Management** 📋
- **Icon**: Clipboard icon
- **Route**: `/mobile/v1/tasks`
- **Access**: All authenticated users
- **Features**:
  - View all tasks (`/tasks`)
  - My tasks (`/tasks/my`)
  - Assigned tasks (`/tasks/assigned`)
  - Create task (`POST /tasks`)
  - Update task (`PUT /tasks/{id}`)
  - Complete task (`POST /tasks/{id}/complete`)
  - Assign task (`POST /tasks/{id}/assign`)
  - View task activities
  - Add activity to task
  - Complete activity
  - Submit activity report

---

### 9. **Meetings & Minutes** 👥
- **Icon**: Group/Meeting icon
- **Route**: `/mobile/v1/meetings` (if available)
- **Access**: All authenticated users
- **Sub-menus**:
  - **Meetings**: View and manage meetings
  - **Pending Approval**: Meetings awaiting approval
  - **Analytics**: Meeting analytics and reports
  - **Categories**: Meeting categories

---

### 10. **Incident Management** ⚠️
- **Icon**: Error/Warning icon
- **Route**: `/mobile/v1/incidents`
- **Access**: All authenticated users
- **Features**:
  - View incidents (`/incidents`)
  - My incidents (`/incidents/my`)
  - Create incident (`POST /incidents`)
  - Update incident (`PUT /incidents/{id}`)
  - Add incident updates (`POST /incidents/{id}/update`)

---

### 11. **Notifications** 🔔
- **Icon**: Bell/Notification icon
- **Route**: `/mobile/v1/notifications`
- **Access**: All authenticated users
- **Features**:
  - View all notifications (`/notifications`)
  - Unread notifications (`/notifications/unread`)
  - Mark as read (`POST /notifications/{id}/read`)
  - Mark all as read (`POST /notifications/read-all`)

---

### 12. **Profile** 👤
- **Icon**: User/Profile icon
- **Route**: `/mobile/v1/profile`
- **Access**: All authenticated users
- **Features**:
  - View profile (`/profile`)
  - Update profile (`PUT /profile`)
  - Update profile photo (`POST /profile/photo`)
  - Change password (`PUT /auth/change-password`)

---

### 13. **System Settings** ⚙️ (System Admin only)
- **Icon**: Settings/Cog icon
- **Access**: System Admin only
- **Sub-menus**:
  - **Manage Users**: User management
  - **System Permissions**: Permission management
  - **Manage Roles**: Role management
  - **Organization Settings**: Organization configuration
  - **System Health**: System status monitoring
  - **System Errors**: Error logs
  - **Activity Log**: System activity logs

---

## Menu Organization Recommendations

### **Bottom Navigation Bar** (Primary Navigation)
For quick access to most frequently used features:
1. **Dashboard** (Home)
2. **Tasks**
3. **Attendance** (Check-in/Check-out)
4. **Notifications**
5. **Profile**

### **Navigation Drawer** (Side Menu)
For complete menu access:
- All main menu items listed above
- Organized in collapsible sections:
  - **Main** (Dashboard, Profile)
  - **Finance** (Petty Cash, Imprest, Refunds, Accounting, Payroll)
  - **HR** (Leave, Attendance, Employees, Permissions, Sick Sheets, Assessments, Recruitment)
  - **Files** (Digital Files, Physical Racks)
  - **Work** (Tasks, Meetings, Incidents)
  - **Settings** (System Settings - Admin only)

---

## Role-Based Menu Visibility

### **All Users** (Staff, HR, Accountant, HOD, CEO, etc.)
- Dashboard
- Petty Cash Management
- Imprest Management
- Refund Requests
- File Management (all sub-menus)
- HR Management (most sub-menus)
- Task Management
- Meetings & Minutes
- Incident Management
- Notifications
- Profile

### **Accountant + System Admin**
- All above +
- Accounting Module

### **HR Officer + System Admin**
- All above +
- Departments Management
- Positions Management
- Recruitment (also available to HOD, CEO)

### **System Admin Only**
- All above +
- System Settings (all sub-menus)

---

## API Endpoints Summary

### **Authentication**
- `POST /mobile/v1/auth/login`
- `POST /mobile/v1/auth/login-otp`
- `POST /mobile/v1/auth/verify-otp`
- `POST /mobile/v1/auth/logout`
- `POST /mobile/v1/auth/forgot-password`
- `POST /mobile/v1/auth/reset-password`
- `PUT /mobile/v1/auth/change-password`

### **Dashboard**
- `GET /mobile/v1/dashboard`
- `GET /mobile/v1/dashboard/stats`
- `GET /mobile/v1/dashboard/notifications`

### **Finance**
- Petty Cash: `/mobile/v1/finance/petty-cash/*`
- Imprest: `/mobile/v1/finance/imprest/*`
- Payroll: `/mobile/v1/finance/payroll/*`

### **HR**
- Leave: `/mobile/v1/leaves/*`
- Attendance: `/mobile/v1/attendance/*`
- Permissions: `/mobile/v1/hr/permissions/*`
- Sick Sheets: `/mobile/v1/hr/sick-sheets/*`
- Assessments: `/mobile/v1/hr/assessments/*`
- Employees: `/mobile/v1/hr/employees/*`
- Recruitment: `/mobile/v1/hr/jobs/*`

### **Files**
- Digital: `/mobile/v1/files/digital/*`
- Physical: `/mobile/v1/files/physical/*`

### **Tasks**
- `/mobile/v1/tasks/*`

### **Incidents**
- `/mobile/v1/incidents/*`

### **Notifications**
- `/mobile/v1/notifications/*`

### **Profile**
- `/mobile/v1/profile/*`

### **Device Management**
- `POST /mobile/v1/device/register` (FCM push notifications)
- `DELETE /mobile/v1/device/unregister`

---

## Implementation Notes

1. **Menu Icons**: Use Material Design icons matching the web application's icon set (Boxicons)
2. **Role-Based Access**: Implement menu visibility based on user roles retrieved from `/mobile/v1/auth/me`
3. **Navigation**: Use Android Navigation Component for fragment-based navigation
4. **Bottom Navigation**: Limit to 5 items (Material Design guideline)
5. **Drawer Menu**: Use NavigationView with menu groups for collapsible sections
6. **Badge Counts**: Show unread notification count on Notifications menu item
7. **Quick Actions**: Implement floating action buttons (FAB) for:
   - Check-in/Check-out (Attendance)
   - Create Task
   - Create Leave Request
   - Create Incident
8. **Offline Support**: Cache menu structure and implement offline menu access
9. **Deep Linking**: Support deep links for notifications and external app launches

---

## Menu Item Priority (for Bottom Navigation)

1. **Dashboard** - Primary landing page
2. **Tasks** - High frequency usage
3. **Attendance** - Daily check-in/out
4. **Notifications** - Real-time updates
5. **Profile** - User account access

---

*Last Updated: Based on current API routes and web sidebar structure*

