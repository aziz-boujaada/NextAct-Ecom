# 🛒 NextAct-Ecom

NextAct-Ecom is an e-commerce management system built to manage clients, products, sales, and stock operations efficiently.

This project follows a separated architecture:

- **Backend API:** Laravel
- **Frontend:** React + TypeScript (hosted in a separate repository)

---

## 🚀 Features

- Client management
- Product management
- Stock tracking
- Sales management
- Sale items management
- Automatic total calculation
- Stock validation before selling
- CRUD operations
- Authentication ready (JWT)

---

## 🏗️ Tech Stack

### Backend
- Laravel
- MySQL
- REST API

### Frontend
- React
- TypeScript
- Lucide React

---

## 📂 Architecture

```bash
Backend Repository (this project)
│
├── app
├── database
├── routes
├── public
└── README.md
```

Frontend is maintained separately.

---

## 🔗 Related Repositories

### Frontend Repository
[NextAct-Ecom Frontend](https://github.com/aziz-boujaada/nectAct-Ecom-front)

---

## ⚙️ Backend Installation

### Clone repository

```bash
git clone https://github.com/your-username/NextAct-Ecom.git
cd NextAct-Ecom
```

### Install dependencies

```bash
composer install
```

### Configure environment

```bash
cp .env.example .env
php artisan key:generate
```

Update database credentials inside `.env`

```env
DB_DATABASE=nextact
DB_USERNAME=root
DB_PASSWORD=
```

### Run migrations

```bash
php artisan migrate
```

### Start server

```bash
npm run dev
```

Server runs on:

```bash
http://127.0.0.1:8000
```

Vite assets run alongside it on `http://127.0.0.1:5174`.

---

## 🔑 Business Logic

### Stock Validation
- Prevents selling quantities greater than available stock
- Handles update operations safely

### Sales Workflow
- A client can have multiple sales
- A sale contains multiple sale items
- Totals are calculated automatically

---

## 📈 Future Improvements

- Dashboard analytics
- Invoice generation
- Role management
- Notifications
- API documentation

---

## 👨‍💻 Author

**Aziz Boujaada**

GitHub: https://github.com/aziz-boujaada

---

## 📄 License

MIT License
