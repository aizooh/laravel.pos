# Mamicar POS — What It Is & User Manual

---

## 📌 Part 1 — What Mamicar POS Is (in a nutshell)

**Mamicar POS** is a simple, fast point-of-sale system built for a **small cyber café + accessories shop**.

It replaces the paper notebook and the receipt pad with one screen that does five things well:

1. **Sell** — ring up products (USB cables, mice, flash disks) and services (printing, scanning, eCitizen) in a single cart, with Cash / M-PESA / Card.
2. **Track stock** — know exactly how many of each item is on the shelf, get warnings when stock runs low, and log every change (sales, purchases, damage, corrections).
3. **Record money** — keep a daily log of money in each bank account, M-PESA, and cash at opening and closing.
4. **Report** — see daily/monthly sales, profit, best sellers, expenses, and staff performance.
5. **Protect** — everything is auditable. Sales can be **voided** (never silently deleted), and every action records who did it and why.

**Two kinds of users:**

- **Admin** (the owner): full access to everything — inventory, prices, expenses, users, reports.
- **Attendant** (staff): sells on the POS, sees their own sales, records daily positions. Cannot touch inventory, prices, or reports.

**What it does NOT do** (by design, for version 1): no customer accounts, no loyalty points, no suppliers, no purchase orders, no multi-branch. All those can be added later if needed.

**Deployed at:** `http://15.207.169.156` (Lightsail, Ubuntu + Nginx + PHP 8.4 + SQLite)

---

## 📖 Part 2 — User Manual

# Mamicar POS — User Manual

## 1. Getting Started

### 1.1 Logging in
1. Open `http://15.207.169.156` in any browser.
2. Enter your **username** and **password**.
3. Click **Sign In**.

> If you don't have credentials, ask the admin to create an account for you.

### 1.2 The navigation bar
After login, the top of every page has a menu:

| Link | Visible to | What it does |
|---|---|---|
| **POS** | Everyone | The selling screen |
| **Sales** | Everyone | List of sales (you see only your own if attendant) |
| **Dashboard** | Everyone | Business snapshot / your shift stats |
| **Inventory** | Admin only | Products management |
| **Services** | Admin only | Cyber services & prices |
| **Expenses** | Admin only | Business expenses |
| **Users** | Admin only | Staff accounts |
| **Reports** | Admin only | Daily / Monthly / Inventory / Void log |
| **Position** | Everyone | Daily financial position (opening/closing) |

To log out, click **Log Out** at the top right.

---

## 2. Selling on the POS

### 2.1 Making a sale — step by step

1. Click **POS** in the menu.
2. You see two grids: **Products** (left) and **Services** (below).
3. Click any product or service to add it to the **Cart** on the right.
   - Click the same item again to increase its quantity.
   - Or type a number directly in the cart's Qty field.
4. To remove an item from the cart, click the **×** on its row.
5. To empty the whole cart, click **Clear**.

### 2.2 Searching quickly

Type in the **Search** box at the top. It filters both grids live by:
- Product name
- SKU (e.g. `USB001`)
- Service name

### 2.3 Completing a sale

1. With items in the cart, click **Checkout**.
2. In the popup, choose the payment method:
   - **Cash**
   - **M-PESA** (enter the M-PESA code if you have it, or leave blank)
   - **Card**
3. Click **Complete Sale**.
4. A **receipt** appears. You can:
   - Click **Print** to print it (thermal printer friendly)
   - Click **New Sale** to start the next customer

### 2.4 Rules to remember

- You **cannot** sell more than what's in stock — the system blocks it.
- Every sale decrements stock automatically.
- Services don't affect stock.
- Invoice numbers are automatic (e.g. `INV-000042`).

---

## 3. Sales History

Click **Sales** in the menu.

- **Admin** sees every sale from every attendant.
- **Attendant** sees only their own sales.
- Use **From** / **To** dates and **Payment** filter to narrow the list.
- Click **Receipt** to view or reprint any sale.

### 3.1 Voiding a sale (admin only)

Voiding means: *"this sale never happened — restore the stock and remove it from reports."*

1. Find the sale in the **Sales** list (or open its Receipt).
2. Click **Void**.
3. Type a **reason** (required — e.g. `Wrong item`, `Customer cancelled`).
4. Confirm.

Result:
- The sale gets a red **VOIDED** badge.
- Product stock is restored automatically.
- The sale disappears from all reports and totals.
- The action is logged (who, when, why).

### 3.2 Unvoiding (admin only)

If you voided a sale by mistake:

1. Tick **Show voided** in the filter.
2. Find the voided sale → click **Unvoid**.
3. Confirm.

Result:
- Stock is deducted again.
- The sale reappears in all reports and totals.
- Logged in the **Void Log** report.

> **Important:** you cannot unvoid if stock has since dropped below the original quantity.

---

## 4. Inventory (Products) — Admin

Click **Inventory** in the menu.

### 4.1 Adding a product

1. Click **+ New Product**.
2. Fill in:
   - **Name** (e.g. `USB-C Cable`)
   - **SKU** (e.g. `USB-C-001`)
   - **Category** (e.g. `Cables`, `Phone Accessories`)
   - **Buying Price** (what you paid)
   - **Selling Price** (what the customer pays)
   - **Opening Stock** (how many you have right now)
   - **Low Stock Alert** (warn when stock falls to this number)
3. Click **Save Product**.

### 4.2 Editing a product

From the Inventory list, click **Edit** next to any product to change its name, price, or settings.

### 4.3 Adding new stock (restocking)

When new stock arrives:

1. Open the product's **Edit** page.
2. Scroll to the **Stock** section.
3. Choose Type: **Purchase** (add stock).
4. Enter the **Quantity** added.
5. Add a reason like `New stock received`.
6. Click **Apply Adjustment**.

### 4.4 Adjusting for damage, loss, or corrections

Same form — pick the appropriate Type:

| Type | When to use |
|---|---|
| **Purchase** | New stock arrived |
| **Return** | Customer returned an item |
| **Damage** | Item broken/lost — subtracts stock |
| **Adjustment** | Manual correction (e.g. recount) |

Every change is logged with **who did it, when, and why** — visible in the product's adjustment history.

### 4.5 Low-stock alerts

- Products at or below their threshold get a red **LOW** badge.
- The Dashboard shows a **Low Stock** card listing all affected products.

---

## 5. Services (Cyber) — Admin

Click **Services** in the menu.

### 5.1 Adding a service
1. Fill in **Service Name** and **Price** at the top.
2. Click **Add Service**.

### 5.2 Editing a service
Change the name, price, or active status **inline** in the table, then click **Save**.

### 5.3 Turning a service off
Untick the **Active** checkbox and Save. It disappears from the POS grid but stays in history.

---

## 6. Expenses — Admin

Click **Expenses** in the menu.

### 6.1 Recording an expense
1. Fill in **Description**, **Amount**, **Category**, **Date**.
2. Categories: Rent, Electricity, Water, Internet, Salary, Transport, Stationery, Airtime, Repairs, Licenses, Other.
3. Click **Save Expense**.

### 6.2 Editing / deleting
- Edit inline in the table → click **Save**.
- To delete, click the red **×** and confirm.

### 6.3 Filtering
Use date range and category filters to see totals for a specific period.

---

## 7. Users — Admin

Click **Users** in the menu.

### 7.1 Adding a user
1. Fill in Name, Username, Password (min 6 chars), Role.
2. Role **Attendant** — POS + own sales + positions.
3. Role **Admin** — full system access.
4. Click **Create User**.

### 7.2 Resetting a password
Click the **New Password** field in the user's row, type a new password, click **Save**. Leave blank to keep the existing one.

### 7.3 Deactivating a user
Untick **Active** → Save. They can no longer log in, but their sales history remains intact.

> **Guard rails:** you cannot deactivate or demote yourself. You also cannot demote the last remaining admin.

### 7.4 Deleting a user
**Users are never deleted** — always deactivated. This preserves the audit trail on sales they made.

---

## 8. Daily Financial Position

Click **Position** in the menu.

This is where you record the money in each payment channel at the start and end of every business day.

### 8.1 Recording the opening (morning)
1. Fill in the amounts for:
   - **KCB Bank**, **Equity Bank**, **Absa Bank**
   - **M-PESA**
   - **Cash**
   - **Other** (optional — add a label like "Cooperative Bank")
2. The total updates automatically as you type.
3. Click **Save Opening**.

### 8.2 Recording the closing (evening)
Same form on the right side — fill in the closing amounts → **Save Closing**.

### 8.3 Today's Change
Once both are saved, a card shows:

- Opening total
- Closing total
- **Change** (increase or decrease during the day)

### 8.4 History
Below the forms, every past day appears with:
- Opening and closing rows
- A green/red badge showing the day's net change

### 8.5 Editing
- **Admin** can edit any record (any date, any user).
- **Attendant** can edit only their **own same-day** records.
- **Only admin** can delete a record.

> **Why this matters:** it gives you a daily snapshot of your money. If closing is much lower than opening + expected, something's off — worth investigating.

---

## 9. Dashboard

Click **Dashboard** in the menu.

### 9.1 Admin view
Four KPI cards:
- **Today's Sales** — total amount and transaction count
- **Gross Profit** — revenue minus cost of goods
- **Expenses** — today only
- **Net** — profit minus expenses

Below that:
- **Payment breakdown** (Cash / M-PESA / Card for today)
- **Low Stock** list with restock shortcuts
- **Recent Sales** (last 10)
- **Quick Nav** buttons

### 9.2 Attendant view
A personal snapshot:
- My sales today (amount + count)
- My recent sales
- **Open POS** button

---

## 10. Reports — Admin

Click **Reports** in the menu. Four report types:

### 10.1 Daily Report
Pick any date. Shows:
- Total Sales, Transactions
- Payment breakdown
- Cost of goods, Gross Profit
- Expenses, Net Profit
- Top-selling items
- Sales by attendant
- Full transaction list + expense list

### 10.2 Monthly Report
Pick any month. Shows:
- KPIs (Sales, Gross Profit, Expenses, Net)
- Daily sales bar chart
- Best-selling products + services
- Sales by attendant
- Expenses by category

### 10.3 Inventory Report
- Total units, cost value, retail value, potential profit
- Table of every product with margin + value
- Filter: low stock only, active only

### 10.4 Void Log
Every void / unvoid action with:
- Date/time
- Action (VOID or UNVOID)
- Invoice number
- Product affected
- Quantity restored/deducted
- User who did it
- Reason

Use this when reconciling cash or investigating discrepancies.

---

## 11. Common Tasks Cheat Sheet

| I want to... | Go to... |
|---|---|
| Sell something | **POS** → click items → **Checkout** |
| Add new product | **Inventory** → **+ New Product** |
| Restock a product | **Inventory** → Edit → **Stock** section → Purchase |
| Add a cyber service | **Services** → fill form → **Add Service** |
| Change a price | **Inventory** or **Services** → Edit inline |
| Void a mistake sale | **Sales** → find it → **Void** (reason required) |
| Undo a void | **Sales** → tick **Show voided** → **Unvoid** |
| Record an expense | **Expenses** → fill form → **Save** |
| Add a new staff member | **Users** → fill form → **Create User** |
| Change a password | **Users** → edit row → **New Password** → Save |
| Disable a staff account | **Users** → untick **Active** → Save |
| Log today's opening cash | **Position** → fill Opening form → **Save** |
| Log today's closing cash | **Position** → fill Closing form → **Save** |
| See today's totals | **Dashboard** |
| See last month's profit | **Reports → Monthly** → pick month |
| See all voids this month | **Reports → Void Log** |

---

## 12. Daily Routine — Suggested

### Morning (opening)
1. Log in as admin or the morning attendant.
2. **Position** → record **Opening** balances.
3. Check **Dashboard** for low stock → restock if needed.
4. Open **POS** ready for the first customer.

### During the day
- Sell on **POS**.
- Void any mistakes immediately (with reason).
- Add new stock when deliveries arrive (**Inventory**).
- Record expenses as they happen (**Expenses**).

### Evening (closing)
1. **Position** → record **Closing** balances.
2. Check **Dashboard** → note today's net.
3. **Sales** → review today's transactions, spot anything odd.
4. Log out.

### Weekly / Monthly
- **Reports → Monthly** — check profit and best sellers.
- **Reports → Void Log** — review any unusual voids.
- **Reports → Inventory** — check stock value, plan restocking.
- Review **Users** — deactivate anyone who's left.

---

## 13. Troubleshooting

| Problem | What to do |
|---|---|
| Can't log in | Check username/password. If disabled, ask admin. |
| Page won't load | Wait 30 seconds, try again. If still down, contact admin. |
| "Only N in stock" | You tried to sell more than available. Reduce quantity. |
| Checkout button does nothing | Refresh the page (Ctrl+Shift+R). |
| Print preview wrong | Use the **Print** button on the receipt — don't use Ctrl+P. |
| Attendant can't see Inventory | Correct — that's admin-only by design. |
| Wrong void | Click **Unvoid** on the sale (admin only). |
| "Cannot unvoid — X only has N in stock" | Product was used for other sales. Void the newer sale first. |

---

## 14. Golden Rules

1. **Never share your password.**
2. **Void, don't delete** — mistakes stay traceable.
3. **Always give a reason** when voiding.
4. **Log in fresh at the start of every shift.**
5. **Record opening and closing positions** every business day.
6. **Log out** when you leave the counter.
7. **Report suspicious voids** to the admin immediately.

---

**Version:** 1.0
**System:** Mamicar POS
**Live at:** `http://15.207.169.156`
**Support:** Contact the admin/owner
