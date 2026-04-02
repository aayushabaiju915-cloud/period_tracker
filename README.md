# 🌸 FlowTrack — Period Tracker Web Application
### A Complete Student Project | PHP + MySQL + XAMPP

---

## 📁 Project Structure

```
period_tracker/              ← Place this in C:\xampp\htdocs\
│
├── index.php                ← Login page (landing page)
├── database.sql             ← SQL to create DB + tables
│
├── config/
│   ├── db.php               ← MySQLi database connection
│   └── auth.php             ← Session helpers, CSRF, prediction logic
│
├── includes/
│   ├── header.php           ← Shared HTML head + navigation bar
│   └── footer.php           ← Shared HTML footer + script tag
│
├── css/
│   └── style.css            ← All styles (variables, layout, components)
│
├── js/
│   └── app.js               ← Form validation, UI interactions
│
└── pages/
    ├── register.php         ← User registration (CREATE user)
    ├── logout.php           ← Destroy session
    ├── dashboard.php        ← Main dashboard after login
    ├── cycles.php           ← View all cycles + delete (READ + DELETE)
    ├── add_cycle.php        ← Add new cycle (CREATE)
    ├── edit_cycle.php       ← Edit existing cycle (UPDATE)
    └── symptoms.php         ← Symptom/mood tracker (CREATE + READ + DELETE)
```

---

## ⚙️ Setup Instructions (XAMPP)

### Step 1 — Start XAMPP
1. Open **XAMPP Control Panel**
2. Click **Start** next to **Apache**
3. Click **Start** next to **MySQL**

### Step 2 — Copy Project Files
Copy the `period_tracker` folder to:
```
C:\xampp\htdocs\period_tracker\
```

### Step 3 — Create the Database
1. Open your browser and go to: `http://localhost/phpmyadmin`
2. Click **New** in the left sidebar
3. Name it `period_tracker` → click **Create**
4. Click the **SQL** tab at the top
5. Open `database.sql` from the project folder
6. Copy all the content and paste it into the SQL box
7. Click **Go** to run it

> ✅ You should see tables: `users`, `cycles`, `symptoms`

### Step 4 — Configure Database (if needed)
Open `config/db.php` and update if your XAMPP setup differs:
```php
define('DB_USER', 'root');   // your MySQL username
define('DB_PASS', '');       // your MySQL password (blank by default)
```

### Step 5 — Open the App
Visit: `http://localhost/period_tracker/`

---

## 🧪 Testing the Application

### Test Account (with sample data)
If you uncomment the sample data in `database.sql`:
- **Email:** `priya@example.com`
- **Password:** `Test@1234`

### Manual Testing — CRUD Operations

| Feature | How to Test |
|---|---|
| **Register** | Go to Register page, fill form, submit |
| **Login** | Use test credentials above |
| **Add Cycle** | Dashboard → "+ Add Cycle" → fill dates → Save |
| **View Cycles** | Nav → "My Cycles" — see table of all cycles |
| **Edit Cycle** | Click ✏️ Edit on any row → change values → Update |
| **Delete Cycle** | Click 🗑️ Delete → confirm dialog → deleted |
| **Add Symptom** | Nav → "Symptoms" → select cycle + date → Save |
| **View Symptoms** | Symptoms page shows history table |
| **Delete Symptom** | Click 🗑️ on any symptom row |
| **Prediction** | Dashboard shows predicted next period date |

---

## 📄 File-by-File Explanation

| File | Purpose |
|---|---|
| `index.php` | Login form; validates credentials using `password_verify()` |
| `database.sql` | SQL script to create all tables with foreign keys |
| `config/db.php` | MySQLi connection; shows friendly error if DB is down |
| `config/auth.php` | `requireLogin()`, `csrfToken()`, `predictNextPeriod()`, `clean()` helpers |
| `includes/header.php` | Shared `<head>`, navbar with login state, flash messages |
| `includes/footer.php` | Closing tags, links `app.js` |
| `css/style.css` | CSS variables, layout, navbar, forms, table, cards, responsive |
| `js/app.js` | Client-side form validation, delete confirmation, symptom chips |
| `pages/register.php` | Registration with server-side validation + bcrypt hashing |
| `pages/logout.php` | Destroys session, redirects to login |
| `pages/dashboard.php` | Stats cards, prediction banner, recent cycles, quick links |
| `pages/cycles.php` | Full cycle history table; handles delete via POST |
| `pages/add_cycle.php` | Form to INSERT a new cycle record |
| `pages/edit_cycle.php` | Pre-filled form to UPDATE an existing cycle |
| `pages/symptoms.php` | Add + view + delete symptom/mood logs |

---

## 🔐 Security Features

- **Password Hashing** — `password_hash()` with `PASSWORD_BCRYPT`
- **Prepared Statements** — All DB queries use MySQLi prepared statements (SQL injection safe)
- **CSRF Tokens** — All POST forms include and verify a CSRF token
- **Session Regeneration** — `session_regenerate_id(true)` on login
- **Output Escaping** — `htmlspecialchars()` / `clean()` on all displayed data
- **Ownership Checks** — Every query filters by `user_id` to prevent data leakage

---

## 💡 How Prediction Works

```
Predicted Date = Last Period Start + Average Cycle Length

Average = mean of last 3 cycle_length values
```

If the predicted date is ≤ 5 days away, the dashboard shows a warning banner.

---

## 🛠️ Technologies Used

| Layer | Technology |
|---|---|
| Frontend | HTML5, CSS3 (custom variables), Vanilla JavaScript |
| Backend | PHP 8+ (no frameworks) |
| Database | MySQL 8 via MySQLi |
| Server | XAMPP (Apache + MySQL) |
| Fonts | Google Fonts (Playfair Display + DM Sans) |

---

## ❓ Common Issues

**"Database connection failed"**
→ Make sure MySQL is started in XAMPP Control Panel
→ Run `database.sql` in phpMyAdmin first

**"Page Not Found"**
→ Confirm folder is at `C:\xampp\htdocs\period_tracker\`
→ URL must be `http://localhost/period_tracker/`

**Blank page**
→ Enable PHP error display: In `config/db.php` add at top:
`ini_set('display_errors', 1); error_reporting(E_ALL);`
