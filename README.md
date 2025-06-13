🍽️ Restaurant Management System
This is a web-based restaurant management system built using HTML, CSS, JavaScript, PHP, and MySQL. It allows Admins, Staff, and Guests to interact with a dynamic system for managing orders, staff, attendance, salaries, menus, and bills.

🔐 Login Types
Admin

Full control over the system

Can manage orders, staff, customers, bills, menu, and attendance

Staff

Can view their attendance, salary, and working hours

Can register new staff accounts

Guest (Customer)

No login required

Can view the menu, place orders, and make payments

🗂️ Main Features
add_menu.php: Add new items to the menu (admin only)

admin_dashboard.php: Admin control panel

guest_dashboard.php: Menu and ordering interface for guests

order_summary.php: Shows order details before payment

generate_receipt.php: Generates a cash payment receipt

staff_dashboard.php: Dashboard for staff with attendance and salary view

register_staff.php: Register new staff members

attendance.php: Mark or view attendance

view_attendance.php: View attendance records

view_salary.php: See staff salary dates

view_bills.php: View completed orders with billing

view_customers.php: See customer order history

view_orders.php: Manage all orders

view_stuffs.php: List all staff

view_menu.php: View current menu items

complete_order.php: Mark order as completed

db.php: Database connection file

login.php / logout.php: Auth system

🗃️ Database
Import the provided SQL file 127_0_0_1.sql to set up the MySQL database. It includes all necessary tables such as:

menu

orders

staff

attendance

salary

customers

⚙️ Tech Stack
Frontend: HTML, CSS, JavaScript

Backend: PHP

Database: MySQL (via phpMyAdmin / XAMPP)

🚀 How to Run
Clone the repo to your htdocs folder (XAMPP)

Import 127_0_0_1.sql into phpMyAdmin

Start Apache and MySQL via XAMPP

Visit http://localhost/{your-folder-name}
