# MasiVera 🛡️
**Security-focused Cvolton GDPS Build**

> ⚠️ **STATUS: WORK IN PROGRESS** / **СТАТУС: В РАЗРАБОТКЕ** ⚠️
> This project is currently under active development. Some features are implemented, others are planned. The code is not yet stable for production use. / Проект находится в активной разработке. Некоторые функции уже реализованы, другие только планируются. Код пока не стабилен для production-использования.

---

### 📖 About the Project / О проекте
**MasiVera** is a custom fork of the classic Cvolton 2.208 engine. Our main goal is to fix critical vulnerabilities, improve server stability, and provide a secure foundation for GDPS owners. We focus on security and transparency.

**MasiVera** — это кастомный форк классического движка Cvolton 2.208. Наша главная цель — исправить критические уязвимости, повысить стабильность сервера и дать безопасную основу для владельцев GDPS. Мы делаем упор на безопасность и прозрачность.

---

### 🧪 Tested Environment / Тестовая среда
This build has been tested on a **dedicated VPS** (Ubuntu 22.04, PHP 8.1, MariaDB) with real-world GDPS traffic to ensure stability and security fixes work as intended.
*Эта сборка была протестирована на **выделенном VPS** (Ubuntu 22.04, PHP 8.1, MariaDB) с реальным трафиком GDPS, чтобы убедиться, что исправления стабильны и работают как задумано.*

---

### ✨ Implemented Features / Реализованные фичи:

*   **🛡️ Basic Dupe Protection**
    Strict SQL and PHP checks prevent negative balances and item duplication. *(Implemented in: `updateGJUserScore`, `getGJLevelScores`)*
    *Жесткие проверки на уровне SQL и PHP предотвращают отрицательные балансы и дублирование.*

*   **⚡ Anti-Cheat (Time & Stats)**
    Rejects impossible completion times. Rejects impossible stat jumps. *(Implemented in: `updateGJUserScore`, `getGJLevelScores`)*
    *Отклоняет невозможные результаты прохождения. Отклоняет невозможные скачки статов.*

*   **🔒 Secure Registration**
    IP rate limiting, cooldowns, and password length checks. *(Implemented in: `registerGJAccount`)*
    *Лимиты по IP, кулдауны и проверка длины пароля.*

*   **🔒 Account Activation Check**
    Login now checks if the account is activated (isActive). *(Implemented in: `loginGJAccount`)*
    *Логин теперь проверяет активацию аккаунта (isActive).*

*   **🧹 Clean Error Handling**
    Removed raw SQL exceptions from player-facing pages. *(Implemented in: `uploadGJComment`, `getGJUserInfo20`)*
    *Убраны сырые ошибки SQL со страниц, видимых игрокам.*

*   **🚨 Anti-Spam (Comments & Levels)**
    Cooldowns for level uploads and comments. *(Implemented in: `uploadGJLevel`, `uploadGJComment`, `uploadGJAccComment`)*
    *Кулдауны для загрузки уровней и комментариев.*

---

### 🚧 Planned Features / Планируемые фичи:

*   **🛡️ Advanced Anti-Raid Shield**
    Advanced IP limits and global raid protection for high-load servers.
    *Продвинутые лимиты по IP и глобальная защита от рейдов.*

*   **📊 Dashboard Improvements**
    Better admin panel with full XSS protection and secure session management.
    *Улучшенная админ-панель с полной защитой от XSS и безопасным управлением сессиями.*

*   **🔧 Full Toolset Security**
    Auditing and securing all tools in the `tools/` directory (reupload, linkAcc, etc.).
    *Аудит и защита всех инструментов в папке `tools/`.*

---

### 📦 Installation (Coming Soon) / Установка (Скоро)
*Documentation and the first stable release will be published here.*
*Документация и первый стабильный релиз будут опубликованы здесь.*

---
### 🤝 Credits / Благодарности
*   Original Cvolton engine by **[Cvolton](https://github.com/Cvolton)**
*   Security fixes, testing, and modifications by **fgn** (MasiGdps Team)

*Made with love for Geometry Dash. Originally started with MasiGdps. 🐾*
