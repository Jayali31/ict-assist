# ICT Assist - Role-Based IT Helpdesk Web Application

A modern, responsive, role-based IT Helpdesk and Service Dispatch Management System built with **React**, **Vite**, **Tailwind CSS**, and **Lucide Icons**.

This system implements the exact workflows and screens designed in your mockups, featuring **3 distinct user roles**:
1. 👤 **Employee Portal** (`A.M. Emandi`)
2. 📋 **Helpdesk Coordinator Portal** (`Operations Dispatcher`)
3. 🛠️ **Staff / Field Technician Console** (`K.P. Ratnasiri`)

---

## 🚀 Key Features

### 1. Employee Dashboard & Services
- **Greeting & Overview Banner**: Fast one-click "+ Create New Request" action.
- **New Request Form**:
  - Auto-fills Branch and Department.
  - Category selection pills: `Hardware`, `Network`, `Software`, `Telephone`.
  - Priority selector: `High`, `Medium`, `Low`.
  - File upload / screenshot attachment simulator.
- **Ticket Tracking & History**:
  - Status badges: `Pending`, `In Progress`, `Resolved`.
  - Search by Ticket ID, Keyword, Branch, or Department.
  - Modal with full ticket details and timeline notes.
- **Notifications**: Grouped by Today / Yesterday with read-state management.
- **Profile & Preferences**:
  - Employee info modal.
  - Notification toggles (Email, SMS, Status updates, Technician alerts).
  - Password change form with validation.

### 2. Coordinator (Helpdesk Manager & Dispatcher)
- **Executive Metrics**: Total Requests, Resolved, Pending, and In Progress.
- **Pending Assignments Queue**: Real-time list of submitted employee tickets awaiting technician dispatch.
- **Assign Technician Modal**:
  - Inspect ticket priority and issue specs.
  - Match with available technicians (`K.P. Ratnasiri`, `A.S. Fernando`, `M.E. Perera`, `N.L. Silva`).
  - Automatic category recommendation tags.
  - One-click dispatch confirmation.
- **Reports & Analytics Dashboard**:
  - Incident breakdown progress bars by Category.
  - Technician performance leaderboard tracking resolved incidents.

### 3. Staff / Technician Console
- **Technician Header**: Availability status toggle (`Available` / `Busy`).
- **Performance Counters**: Total Assigned, Currently Active, and Completed jobs.
- **ACTIVE TASK Highlight**:
  - Focus card for the highest priority job on site.
  - **Update Progress Modal**: Transition tickets (e.g. *In Progress* &rarr; *Resolved*) and record diagnostic notes.
- **Task Queue**: Real-time list of all tickets queued for the technician.

### 4. Interactive Live State
- Fully connected through `React Context` + `localStorage`.
- Submit a ticket as an **Employee**, immediately see it under **Coordinator** to assign it, and switch to **Staff** to resolve it!
- Includes quick 1-click demo role switchers in the sidebar and login page for presentation and testing.

---

## 📦 How to Run Locally

### Prerequisites
- Node.js installed (v18 or higher recommended).
- PowerShell or Terminal.

### Commands

1. Open your terminal in this project folder:
   ```bash
   cd "C:\Users\jayal\.gemini\antigravity\scratch\ict-assist"
   ```

2. Install dependencies (on Windows PowerShell, use `npm.cmd` if script execution is restricted):
   ```powershell
   npm.cmd install
   ```
   *(or `npm install`)*

3. Start the local development server:
   ```powershell
   npm.cmd run dev
   ```

4. Open your browser and navigate to:
   ```
   http://localhost:3000
   ```

---

## 🤝 GitHub Collaboration Guide (For You & Your Fellow Intern)

Follow these exact steps to push this code to GitHub and collaborate together smoothly:

### Phase 1: Initialize Git and Push to GitHub (Intern 1)

1. **Initialize Git inside the project folder**:
   ```powershell
   git init
   ```

2. **Stage and commit the files**:
   ```powershell
   git add .
   git commit -m "feat: initial commit for ICT Assist role-based helpdesk"
   ```

3. **Create a new repository on GitHub**:
   - Go to [github.com/new](https://github.com/new).
   - Repository name: `ict-assist`
   - Visibility: Choose **Public** or **Private**.
   - **Do NOT** check "Add a README file" or ".gitignore" (we already have them).
   - Click **Create repository**.

4. **Link and push your local code to GitHub**:
   ```powershell
   git branch -M main
   git remote add origin https://github.com/YOUR_USERNAME/ict-assist.git
   git push -u origin main
   ```
   *(Replace `YOUR_USERNAME` with your GitHub username).*

5. **Invite your fellow intern**:
   - In your GitHub repo, go to **Settings** &rarr; **Collaborators** &rarr; **Add people**.
   - Type your fellow intern's GitHub username or email and click **Add**.
   - Your partner must accept the invite via their email or GitHub notifications.

---

### Phase 2: How Your Fellow Intern Clones and Sets Up (Intern 2)

1. Open terminal on their machine and clone the repo:
   ```bash
   git clone https://github.com/YOUR_USERNAME/ict-assist.git
   cd ict-assist
   ```

2. Install dependencies:
   ```bash
   npm install
   ```
   *(or `npm.cmd install` on Windows)*

3. Run the development server:
   ```bash
   npm run dev
   ```

---

### Phase 3: Professional Team Branching Workflow (Avoid Conflicts!)

> [!TIP]
> **Best Practice:** Never commit directly to `main`. Always create a feature branch for each task, commit to that branch, push it, and merge via Pull Request.

#### Example: Intern 1 works on Coordinator features
```bash
# 1. Ensure you have the latest main
git checkout main
git pull origin main

# 2. Create a new branch
git checkout -b feature/coordinator-filters

# 3. Make your code changes...

# 4. Stage and commit
git add .
git commit -m "feat: add branch filtering to coordinator view"

# 5. Push branch to GitHub
git push -u origin feature/coordinator-filters
```

#### Example: Intern 2 works on Technician features
```bash
# 1. Create a new branch
git checkout main
git pull origin main
git checkout -b feature/technician-upload

# 2. Make your code changes...

# 3. Stage and commit
git add .
git commit -m "feat: add photo attachment preview in staff modal"

# 4. Push branch to GitHub
git push -u origin feature/technician-upload
```

#### Merging Changes (Pull Requests)
1. Go to your repository on GitHub.
2. You will see a button **Compare & pull request**. Click it.
3. Your partner can review the changes, leave comments, and click **Merge pull request**.
4. Both interns can then sync their local `main`:
   ```bash
   git checkout main
   git pull origin main
   ```

---

## 🗂️ Project Structure

```
ict-assist/
├── .gitignore                     # Prevents node_modules from being pushed to Git
├── README.md                      # Project documentation and team guide
├── index.html                     # HTML5 template with Inter typography
├── package.json                   # React, Lucide, Tailwind dependencies
├── vite.config.js                 # Vite bundler configuration
├── tailwind.config.js             # Color palette matching mockup design
├── postcss.config.js              # PostCSS plugins
└── src/
    ├── main.jsx                   # Application entry point with AppProvider
    ├── App.jsx                    # Root layout, role routing, and view switching
    ├── index.css                  # Tailwind styles and custom scrollbars
    ├── data/
    │   └── initialData.js         # Pre-loaded mock tickets, technicians, notifications
    ├── context/
    │   └── AppContext.jsx         # Central reactive state + localStorage persistence
    └── components/
        ├── common/
        │   ├── Badge.jsx          # Status, Priority, and Availability pills
        │   ├── Modal.jsx          # Reusable modal container
        │   ├── Navbar.jsx         # Top navigation header with profile & notifications
        │   └── Sidebar.jsx        # Dark navigation sidebar matching mockup
        ├── employee/
        │   ├── EmployeeDashboard.jsx    # Employee welcome hero & quick hubs
        │   ├── NewRequestForm.jsx       # Incident submission form
        │   ├── TicketStatusView.jsx     # Filterable ticket status table & details modal
        │   ├── NotificationSettings.jsx # Email & SMS preference toggles
        │   └── ChangePasswordView.jsx   # Password change form
        ├── coordinator/
        │   ├── CoordinatorDashboard.jsx # Dispatcher metrics & pending tickets
        │   ├── AssignStaffModal.jsx     # Technician allocation modal
        │   └── ReportsView.jsx          # Category distribution & staff leaderboard
        ├── staff/
        │   ├── StaffDashboard.jsx       # Active task highlight & progress update modal
        │   └── TaskQueueView.jsx        # Full technician task table
        └── shared/
            ├── LoginPage.jsx            # Sign in screen with role switcher & demo logins
            ├── NotificationsView.jsx    # Grouped Today/Yesterday alert feed
            └── ProfileView.jsx          # Profile card and quick navigation
```

---

## 💡 Future Enhancements
When you and your fellow intern are ready to connect a backend:
- **Backend API**: Build an Express.js (Node.js) or FastAPI (Python) backend to replace `initialData.js` and `localStorage`.
- **Database**: Connect PostgreSQL, MySQL, or MongoDB to store tickets, users, and logs persistently across multiple machines.
- **Authentication**: Integrate JWT (JSON Web Tokens) or Firebase Auth for secure passwords.
