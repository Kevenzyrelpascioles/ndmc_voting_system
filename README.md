# 🗳️ NDMC Voting System

<div align="center">
  
  ![NDMC Logo](https://img.shields.io/badge/NDMC-Voting%20System-green?style=for-the-badge&logo=checkmarx&logoColor=white)
  ![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
  ![MySQL](https://img.shields.io/badge/MySQL-00000F?style=for-the-badge&logo=mysql&logoColor=white)
  ![Bootstrap](https://img.shields.io/badge/Bootstrap-563D7C?style=for-the-badge&logo=bootstrap&logoColor=white)
  ![jQuery](https://img.shields.io/badge/jQuery-0769AD?style=for-the-badge&logo=jquery&logoColor=white)
  
  <h3>🎓 A Modern Electronic Voting System for Notre Dame of Midsayap College 🎓</h3>
  
  [![License](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
  [![Status](https://img.shields.io/badge/Status-Active-success.svg)]()
  [![PRs Welcome](https://img.shields.io/badge/PRs-welcome-brightgreen.svg)](CONTRIBUTING.md)
  
  <p align="center">
    <a href="#-features">Features</a> •
    <a href="#-demo">Demo</a> •
    <a href="#-quick-start">Quick Start</a> •
    <a href="#-technology-stack">Tech Stack</a> •
    <a href="#-installation">Installation</a> •
    <a href="#-modules">Modules</a>
  </p>

</div>

---

## 📋 System Description

<img align="right" width="100" height="100" src="https://media.giphy.com/media/WUlplcMpOCEmTGBtBW/giphy.gif">

The **NDMC Voting System** is a comprehensive web-based application designed for Notre Dame of Midsayap College (NDMC) to conduct secure and efficient electronic voting for student elections. The system provides a dual-environment solution that works both offline (localhost development) and online (production hosting) with automatic environment detection.

### 🎯 Core Objectives
- 🔒 **Secure** - Multi-layer security with session management
- ⚡ **Fast** - Optimized performance for quick voting
- 📱 **Responsive** - Works on all Laptop
- 🌐 **Flexible** - Dual environment support


---

## ✨ Features

<table>
<tr>
<td width="50%">

### 👥 For Voters
- 🗳️ **Intuitive Voting Interface**
- ✅ **Vote Confirmation System**
- 📱 **Mobile-Responsive Design**
- 🔍 **Real-time Validation**
- 🎯 **Multi-position Support**

</td>
<td width="50%">

### 👨‍💼 For Administrators
- 📊 **Comprehensive Dashboard**
- 👤 **Complete Voter CRUD**
- 🎖️ **Candidate Management**
- 📈 **Real-time Statistics**
- 📝 **Detailed Reports**
- 🔍 **Activity Logging**

</td>
</tr>
</table>

### 🌟 Key Highlights



<details>
<summary>🎨 User Experience</summary>

- ✅ Intuitive Interface Design
- ✅ Mobile-First Approach
- ✅ Real-time Feedback
- ✅ Progress Indicators
- ✅ Confirmation Dialogs
- ✅ Error Handling

</details>

<details>
<summary>⚙️ System Features</summary>

- ✅ Dual Environment Support
- ✅ Automatic Configuration
- ✅ Department Hierarchy
- ✅ Multi-position Elections
- ✅ Export Capabilities
- ✅ Batch Operations

</details>

---



<details>
<summary>📸 View More Screenshots</summary>

<table>
<tr>
<td align="center">
  <img src="https://via.placeholder.com/300x200/2196F3/FFFFFF?text=Voter+Login" alt="Voter Login"><br>
  <b>🔐 Voter Login</b>
</td>
<td align="center">
  <img src="https://via.placeholder.com/300x200/FF9800/FFFFFF?text=Voting+Process" alt="Voting Process"><br>
  <b>🗳️ Voting Process</b>
</td>
<td align="center">
  <img src="https://via.placeholder.com/300x200/9C27B0/FFFFFF?text=Admin+Dashboard" alt="Admin Dashboard"><br>
  <b>📊 Admin Dashboard</b>
</td>
</tr>
</table>

</details>

---

## 🚀 Quick Start

```bash
# 1️⃣ Clone the repository
git clone https://github.com/your-username/ndmc_voting_system.git

# 2️⃣ Move to your web server directory
cp -r ndmc_voting_system /path/to/xampp/htdocs/

# 3️⃣ Import database
mysql -u root -p < setup_localhost.sql

# 4️⃣ Start your server and visit
http://localhost/ndmc_voting_system/
```

### 🔑 Default Credentials
```yaml
Username: admin2
Password: admin2
```

---

## 💻 Technology Stack

<div align="center">

| Category | Technologies |
|----------|-------------|
| **Backend** | ![PHP](https://img.shields.io/badge/PHP-777BB4?style=flat-square&logo=php&logoColor=white) ![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=flat-square&logo=mysql&logoColor=white) |
| **Frontend** | ![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=flat-square&logo=html5&logoColor=white) ![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=flat-square&logo=css3&logoColor=white) ![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=flat-square&logo=javascript&logoColor=black) ![jQuery](https://img.shields.io/badge/jQuery-0769AD?style=flat-square&logo=jquery&logoColor=white) ![Bootstrap](https://img.shields.io/badge/Bootstrap-563D7C?style=flat-square&logo=bootstrap&logoColor=white) |
| **Tools** | ![XAMPP](https://img.shields.io/badge/XAMPP-FB7A24?style=flat-square&logo=xampp&logoColor=white) ![Git](https://img.shields.io/badge/Git-F05032?style=flat-square&logo=git&logoColor=white) ![VS Code](https://img.shields.io/badge/VS%20Code-007ACC?style=flat-square&logo=visual-studio-code&logoColor=white) |

</div>

---

## 📦 Installation

### Prerequisites ✅

- 🖥️ XAMPP/WAMP/MAMP installed
- 🐘 PHP 7.2 or higher
- 🗄️ MySQL 5.7 or higher
- 🌐 Modern web browser

### 🔧 Step-by-Step Installation

<details>
<summary><b>1️⃣ Download & Setup</b></summary>

```bash
# Clone the repository
git clone https://github.com/your-username/ndmc_voting_system.git

# Navigate to the project
cd ndmc_voting_system

# Copy to web server directory
# For XAMPP (Windows)
cp -r . C:/xampp/htdocs/ndmc_voting_system/

# For XAMPP (Mac/Linux)
cp -r . /opt/lampp/htdocs/ndmc_voting_system/
```

</details>

<details>
<summary><b>2️⃣ Database Configuration</b></summary>

1. Start Apache and MySQL from XAMPP Control Panel
2. Open phpMyAdmin: `http://localhost/phpmyadmin`
3. Create a new database named `ndmc_voting`
4. Import `setup_localhost.sql`:
   - Click on the database
   - Go to "Import" tab
   - Choose file: `setup_localhost.sql`
   - Click "Go"

</details>

<details>
<summary><b>3️⃣ Verify Installation</b></summary>

1. Visit: `http://localhost/ndmc_voting_system/setup_localhost.php`
2. Check all items show ✅ green checkmarks
3. If any issues ❌, follow the troubleshooting guide

</details>

<details>
<summary><b>4️⃣ Access the System</b></summary>

- 🗳️ **Voter Portal**: `http://localhost/ndmc_voting_system/`
- 👨‍💼 **Admin Panel**: `http://localhost/ndmc_voting_system/admin/`
- 📊 **Setup Check**: `http://localhost/ndmc_voting_system/setup_localhost.php`

</details>

---

## 📊 Modules

### ✅ Implemented Modules

<details>
<summary><b>👥 Voter Management CRUD</b></summary>

#### 🆕 CREATE - Add New Voters
- **Files**: `admin/new_voter.php`, `admin/save_voter.php`
- ✨ Auto-generated secure passwords
- 🏢 Department & course assignment
- ✔️ Duplicate prevention
- 📝 Form validation

#### 📖 READ - View Voter Lists
- **File**: `admin/voter_list.php`
- 🔍 Advanced search functionality
- 🏷️ Multiple filter options
- 📊 Voting status tracking
- 📥 Excel export capability

#### ✏️ UPDATE - Edit Voter Information
- **File**: `admin/edit_voter.php`
- 👤 Complete profile editing
- 🔐 Password management
- 🏢 Department transfers
- 📝 History tracking

#### 🗑️ DELETE - Remove Voters
- **File**: `admin/delete_voter.php`
- ⚠️ Confirmation dialogs
- 🔗 Cascade handling
- 📝 Audit logging

</details>

<details>
<summary><b>🎖️ Candidate Management</b></summary>

- CRUD operations
- Photo upload functionality
- Position management
- Voting statistics

</details>

<details>
<summary><b>🗳️ Voting Process</b></summary>

- Multi-step voting interface
- Real-time validation
- Vote confirmation system
- Security measures

</details>

<details>
<summary><b>📈 Reports & Analytics</b></summary>

- Real-time vote counting
- Detailed canvassing reports
- Export capabilities
- Visual statistics

</details>

---

## 🏗️ Project Structure

## 🙏 Acknowledgments

- 🏫 Notre Dame of Midsayap College
- 👥 All contributors and testers
- 📚 Open source community

---

<div align="center">

### 🌟 Star this repository if you found it helpful! 🌟

<img src="https://media.giphy.com/media/LnQjpWaON8nhr21vNW/giphy.gif" width="60"> <em><b>Made with ❤️ for NDMC</b></em>

[![GitHub stars](https://img.shields.io/github/stars/your-username/ndmc_voting_system?style=social)](https://github.com/your-username/ndmc_voting_system/stargazers)
[![GitHub forks](https://img.shields.io/github/forks/your-username/ndmc_voting_system?style=social)](https://github.com/your-username/ndmc_voting_system/network/members)

</div> 