# GUI Manager User Guide

The **SteelFlow MRP** GUI Manager allows users to customize their interface experience to suit their environment and preferences.

## 🌙 Theme Switching (Dark & Light Mode)

SteelFlow MRP features a native Dark Mode built with Tailwind CSS.

### **How to Toggle**
1. Locate the **Theme Toggle** button in the top navigation bar (near your user profile).
2. Click the 🌙 icon to switch to **Dark Mode**.
3. Click the ☀️ icon to switch back to **Light Mode**.

### **Persistence**
Your theme preference is saved to your user account in the cloud. Whether you log in from your workstation or a mobile tablet on the shop floor, your preference will follow you.

---

## 🏗️ Layout Customization (Planned)

We are working on extending the GUI Manager to support:

- **Layout Density**: Switch between `Compact` (high information density for estimators) and `Spacious` (large touch targets for shop floor tablets).
- **Sidebar Collapsing**: Maximize your primary workspace by collapsing navigation.
- **Color Accents**: Choose custom accent colors for your organization.

---

## 🛠️ Technical Details (For Developers)

### **State Management**
The system uses a unified approach to UI state:
1. **Frontend**: The `ThemeToggle.vue` component uses Inertia's `useForm` to send updates.
2. **Backend**: `SettingsController.php` merges new settings into the user's `settings` JSON column.
3. **Rendering**: The `AppLayout.vue` component reads the settings from the auth object and applies the `dark` class to the root HTML element.

### **Database Schema**
Settings are stored in the `users` table:
```sql
ALTER TABLE users ADD settings JSON;
```

### **Manual Override**
If you need to force a theme via the console:
```javascript
// Example: Force Dark Mode
document.documentElement.classList.add('dark');
```

---
*Precision tools for precise industry.*
