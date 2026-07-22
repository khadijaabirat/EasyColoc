 # 🏠 EasyColoc — Smart Shared Housing Management Platform

EasyColoc is a full-stack web application developed with **Laravel** to simplify shared housing management by automating expense tracking, debt calculation, reimbursement management, and member collaboration.

The platform provides a centralized solution where roommates can manage shared expenses, monitor balances, settle debts, and maintain financial transparency without manual calculations.

Developed as an **individual academic project**, EasyColoc demonstrates modern Laravel development practices, clean MVC architecture, business logic implementation, and responsive user interface design.

---

# 📸 Application Preview


## Dashboerd

<img width="2560" height="1600" alt="127 0 0 1_8000_dashboard(Nest Hub Max)" src="https://github.com/user-attachments/assets/7d0744ac-ca81-4c45-bd98-3496672638f1" />
ard

 
 <img width="2880" height="1826" alt="127 0 0 1_8000_dashboard (1)" src="https://github.com/user-attachments/assets/0be0d1a6-321d-42d5-9102-fae550a1d720" />

---

## Shared House


 <img width="2560" height="1600" alt="127 0 0 1_8000_dashboard(Nest Hub Max)" src="https://github.com/user-attachments/assets/6cefe60e-503f-46de-adc8-d3fbbfcee9f1" />

 <img width="2560" height="1600" alt="127 0 0 1_8000_colocations_create(Nest Hub Max)" src="https://github.com/user-attachments/assets/54411ed2-6c54-4e1f-920b-2ba812f88279" />

<img width="2560" height="1992" alt="127 0 0 1_8000_colocations(Nest Hub Max)" src="https://github.com/user-attachments/assets/9a353c32-33c4-43c8-94c0-b48e385b9033" />


--- 


## Expenses

 <img width="2880" height="2198" alt="127 0 0 1_8000_colocations_1 (5)" src="https://github.com/user-attachments/assets/acb49996-eec7-4e22-91e7-ed8db72acdbe" />

---

## Settlement View

 <img width="2880" height="2608" alt="127 0 0 1_8000_colocations_1 (8)" src="https://github.com/user-attachments/assets/4acf9ab1-ddc4-4233-8b5c-d218b3bb0450" />


---

## Invitations

 <img width="2560" height="1600" alt="127 0 0 1_8000_dashboard(Nest Hub Max)" src="https://github.com/user-attachments/assets/2732adac-398b-43a4-948e-4b31908f3dc1" />

<img width="2560" height="1600" alt="mailtrap io_sandboxes_4807229_messages_5605962776(Nest Hub Max)" src="https://github.com/user-attachments/assets/81b9c0fc-bc5a-48c0-8fed-4aaebb351083" />


<img width="2880" height="2864" alt="127 0 0 1_8000_colocations_1 (4)" src="https://github.com/user-attachments/assets/5c12d985-6f63-4abd-b79c-fce09d4ffde4" />



---

## Global Admin Dashboard

 <img width="2880" height="2608" alt="127 0 0 1_8000_colocations_1 (8)" src="https://github.com/user-attachments/assets/79cda98e-d536-4d14-8223-45c3e294eba7" />


---

## Profile
- If a member leaves the shared house with unpaid debts, their reputation decreases by **-1**.
- When a member leaves, any remaining shared expenses are automatically redistributed among the remaining active members.
- All balances and settlements are recalculated automatically to reflect the updated group.
   
   <img width="2560" height="2890" alt="127 0 0 1_8000_profile(Nest Hub Max)" src="https://github.com/user-attachments/assets/8fd07581-70c9-418b-820c-26b39735e081" />

  <img width="2560" height="1838" alt="127 0 0 1_8000_colocations_1(Nest Hub Max)" src="https://github.com/user-attachments/assets/e1c959dd-c050-4ca3-aafd-99718235e4cc" />

---
# 📖 Project Overview

Managing expenses in shared housing often leads to manual calculations, misunderstandings, and financial disputes.

EasyColoc eliminates these problems by providing an intelligent expense management platform that automatically calculates balances, identifies who owes whom, tracks reimbursements, and manages member participation through a secure and user-friendly interface.

The application supports different user roles, invitation-based collaboration, financial reputation management, and administrative moderation.

---

# ✨ Key Features

## 👤 User Management

* User registration and authentication
* Profile management
* Automatic Global Admin assignment for the first registered user
* Secure account management
* Banned user protection

---

## 🏡 Shared Housing Management

* Create shared houses
* Update shared house information
* Cancel shared houses
* Leave shared houses
* Automatic Owner assignment
* Restrict users to one active shared house

---

## ✉️ Invitation System

* Invitation by email
* Secure invitation token
* Accept or decline invitations
* Email verification
* Duplicate membership prevention

---

## 💰 Expense Management

* Create shared expenses
* Edit expenses
* Delete expenses
* Expense categories
* Expense history
* Monthly expense filtering
* Statistics by category
* Monthly spending overview

---

## ⚖️ Smart Balance Calculation

The platform automatically calculates:

* Total paid by each member
* Individual share
* Current balance
* Outstanding debts
* Simplified reimbursement suggestions
* "Who owes whom" overview

---

## 💳 Payment Management

* Mark settlements as paid
* Automatic balance updates
* Debt reduction
* Payment tracking

---

## ⭐ Reputation System

Financial behavior directly affects each member's reputation.

* Positive reputation for responsible behavior
* Reputation penalty for leaving with unpaid debts
* Automatic reputation adjustment

---

## 👑 Role Management

### Member

* Join a shared house
* Add expenses
* View balances
* Pay debts
* Leave the shared house

### Owner

* Create shared houses
* Invite members
* Remove members
* Manage categories
* Cancel shared houses

### Global Administrator

* Platform statistics
* User moderation
* Ban and unban users
* Monitor shared houses
* Manage platform activity

---

# 🛠 Tech Stack

## Backend

* Laravel
* PHP
* Laravel Breeze
* Eloquent ORM

---

## Frontend

* Blade
* Tailwind CSS
* HTML5
* CSS3
* JavaScript

---

## Database

* MySQL

---

## Development Tools

* Git
* GitHub
* Composer

---

# 🏗 Architecture

EasyColoc follows Laravel's MVC architecture.

```text
app/
├── Http/
│   ├── Controllers/
│   ├── Middleware/
│   └── Requests/
│
├── Models/
│
├── Mail/
│
├── Policies/
│
resources/
├── views/
│
routes/
├── web.php
│
database/
├── migrations/
├── seeders/
```

---

# 🗄 Database Overview

Main entities:

* Users
* Shared Houses
* Memberships
* Expenses
* Categories
* Invitations
* Settlements
* Payments

---

# 📌 Business Rules

* One active shared house per user.
* Invitation tokens are unique and secure.
* Only invited users can join a shared house.
* Expense balances are automatically recalculated.
* Owners cannot leave without transferring ownership.
* Financial reputation changes according to member behavior.
* Platform administrators can moderate users globally.

---

# 🔒 Security Features

* Authentication with Laravel Breeze
* CSRF Protection
* XSS Protection
* Server-side validation
* Authorization Policies
* Secure password hashing
* Route protection with Middleware

---

# 💡 Technical Highlights

* Laravel MVC Architecture
* Authentication & Authorization
* Eloquent Relationships
* Complex Business Logic
* Invitation Token System
* Automatic Settlement Algorithm
* Reputation Engine
* Responsive Dashboard
* RESTful Routing
* Modular Code Structure

---

# 📱 Responsive Design

Optimized for:

* Desktop
* Laptop
* Tablet
* Mobile

---

# ⚡ Performance & Best Practices

* Clean MVC architecture
* SOLID-inspired organization
* Reusable Blade components
* Responsive layouts
* Semantic HTML
* Optimized database queries
* Organized project structure
* Git version control
* Maintainable codebase

---

# ⚙️ Installation

Clone the repository

```bash
git clone https://github.com/your-username/EasyColoc.git
```

Navigate to the project

```bash
cd EasyColoc
```

Install dependencies

```bash
composer install
```

Copy the environment file

```bash
cp .env.example .env
```

Generate the application key

```bash
php artisan key:generate
```

Configure your database inside the `.env` file.

Run migrations and seeders

```bash
php artisan migrate --seed
```

Start the development server

```bash
php artisan serve
```

---

# 📂 Project Structure

```text
EasyColoc/
│
├── app/
├── bootstrap/
├── config/
├── database/
├── public/
├── resources/
├── routes/
├── storage/
├── tests/
├── artisan
├── composer.json
└── README.md
```

---

# 🚧 Challenges

* Designing an accurate settlement algorithm.
* Managing complex Eloquent relationships.
* Implementing invitation workflows.
* Synchronizing balances after every expense.
* Preventing inconsistent financial states.
* Building a scalable role-based permission system.

---

# 🚀 Future Improvements

* Stripe payment integration
* Real-time notifications
* Shared calendar
* Expense export (PDF / Excel)
* Mobile application
* Multi-currency support
* Push notifications
* REST API for mobile clients

---

# 👩‍💻 Author

**Khadija Abirat**

Full-Stack Web Developer

Passionate about building scalable Laravel applications, solving real-world problems, and designing clean, maintainable software architectures.

---

# 📄 License

This project was developed for educational purposes as part of the **YouCode Full-Stack Web Development Program**.

---

# 🙏 Acknowledgements

Special thanks to **YouCode** and our instructors for their guidance and continuous support throughout the project development process.
 
 
