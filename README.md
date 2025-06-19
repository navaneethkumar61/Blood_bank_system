# 🩸 Blood Bank Management System

A web-based application to manage blood donations, donor and recipient records, and inventory levels in a centralized and secure way.

## 🚀 Features

- 🔐 User Registration & Login with Role-Based Access (Donor, Recipient, Admin)
- 🩸 Track Available Blood Units by Type
- 🧾 Maintain Donor and Recipient Records
- 🛠️ Admin Panel to Manage Inventory and Approvals
- 📧 OTP Verification via Email (using EmailJS)

## 🏗️ Tech Stack

- **Frontend**: HTML, CSS, JavaScript
- **Backend**: PHP 
- **Database**: MySQL 
- **Email Services**: EmailJS (for OTP)
- **Hosting**: Netlify 


## 🔧 Setup & Installation

1. **Clone the Repository**
```
git clone https://github.com/your-username/blood-bank-system.git
cd blood-bank-system
```

2. **Set Up Backend (PHP + MySQL)**
   - Use XAMPP to start Apache & MySQL.
   - Import `blood_bank.sql` into phpMyAdmin.

3. **Frontend**
   - Open `frontend/index.html` in a browser.
   - Ensure HTML forms link to correct PHP files.

4. **Configure EmailJS**
   - Create an EmailJS account.
   - Replace public key, service ID, and template ID in `scripts.js`.

## 🧪 Usage

- **Donors** register and verify OTP before booking donation appointments.
- **Recipients** request blood with type/location.
- **Admins** manage requests, inventory, and donor logs.

## 📄 License

This project is licensed under the [MIT License](LICENSE).

## 🙋‍♂️ Author

- **Navaneeth Kumar** – [GitHub](https://github.com/your-username)

## 💡 Future Enhancements

- 📱 Mobile Responsive Design
- 🧬 Blood Compatibility Matching
- 🗺️ Nearby Blood Bank Mapping
- 🔔 Real-time Notifications
