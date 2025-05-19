# Flip Magento 2 Payment Module

[![Latest Version](https://img.shields.io/github/v/release/flip-id/Flip-Magento2-Payment-Module)](https://github.com/flip-id/Flip-Magento2-Payment-Module/releases)
[![License](https://img.shields.io/github/license/flip-id/Flip-Magento2-Payment-Module)](LICENSE)

This is the **Flip Payment Module for Magento 2**, enabling seamless integration of **Flip's payment services** into your Magento store.

## Features
- Accept payments via **Flip** in Magento 2.
- Automatic payment status updates.
- Secure and reliable payment processing.

---

## 📌 Requirements
- **Magento 2.4.x**
- **PHP 8.x**
- **Flip API Key** (Get yours from [Flip Dashboard](https://business.flip.id/sandbox/credentials))

---

## 🔧 Installation

### A. Manual Installation
1. Download the module from [GitHub Releases](https://github.com/flip-id/Flip-Magento2-Payment-Module/releases).
2. Extract and copy the files into:
   ```
   app/code/FlipForBusiness/Checkout/
   ```
3. Run the following Magento CLI commands:
   ```sh
   bin/magento module:enable FlipForBusiness_Checkout
   bin/magento setup:upgrade
   bin/magento cache:flush
   ```

---

## ⚙️ Configuration
1. Log in to Magento **Admin Panel**.
2. Navigate to:  
   **Stores** → **Configuration** → **Sales** → **Payment Methods**.
3. Find **Flip for Business** and expand the settings.
4. Configure the following:
   - **Is Live Mode**: Choose **No** for Test Mode/Sandbox, or **Yes** for Production
   - **Flip Business ID**: *Input Your Flip Business ID*
   - **API Secret Key**: *Your Flip API Key*
   - **Validation Key**: *Your Flip Validation Key*
5. Click **Save Config** and **Flush Cache**.

---

## 📢 Usage
Once configured, customers will see **Flip Online Payment** as a payment option during checkout. Orders paid via Flip will automatically update their status in Magento.

---

## 🔄 Set up Payment Callback URL on Flip Dashboard

To ensure **payment status updates** are received by your Magento store, you need to set up the **Webhook URL** on the **Flip Dashboard**. The Webhook Notification URL allows Flip to send real-time updates to your Magento store regarding the status of transactions processed through the payment extension. This ensures that your store reflects accurate transaction statuses for completed, pending, or failed payments. Follow these steps:

1. Log in to your **Flip Dashboard** at [Flip Dashboard](https://business.flip.id/sandbox/credentials).
2. Navigate to the **Callback** section.
3. Scroll down to the **Accept Payment** section.
4. Enter the following **Callback URL**:
   ```
   https://yourstore.com/flipforbusiness/payment/callback
   ```
   Replace `yourstore.com` with your actual Magento store domain.

5. Click **Save and Test Callback** button.

This URL will handle Flip's **payment callback** notifications, ensuring that payment status is updated in your Magento store after a transaction is completed.

---

## 🛠 Troubleshooting
### 1. Payments Not Updating
- Ensure **Webhook URL** is set in Flip dashboard:
  ```
   https://yourstore.com/flipforbusiness/payment/callback
  ```
- Make sure your **API Key** and **Validation Key** is correct.

### 2. "Module Not Found" Error
- Run:
  ```sh
  bin/magento module:status | grep FlipForBusiness_Checkout
  ```
  If disabled, enable it using:
  ```sh
  bin/magento module:enable FlipForBusiness_Checkout
  ```

### 3. Cache Issues
Try clearing the Magento cache:
```sh
bin/magento cache:flush
bin/magento cache:clean
```

---

## 📝 License
This project is licensed under the [MIT License](LICENSE).

---

## 🤝 Contributing
We welcome contributions! Please fork the repository and submit a pull request.
