# WP Netlify Webhook Deploy

[![Banner Image](https://github.com/lukethacoder/wp-netlify-webhook-deploy/blob/master/assets/banner-1544x500.jpg)](https://github.com/lukethacoder/wp-netlify-webhook-deploy)

## Description

Easily deploy static sites using Wordpress and Netlify

**Build** Quickly and easily send webhooks to build your Netlify

**Status** Check the status of your latest build to see if it was successful without even leaving Wordpress

---

## Installation

From your WordPress dashboard

1. ~~**Visit** Plugins > Add New~~
2. ~~**Search** for "Deploy Webhook Button"~~
3. ~~**Activate** Deploy Webhook Button from your Plugins page~~
4. **Download** this repo 
5. **Upload** the plugin to your WordPress instance 
6. **Activate** the plugin 
7. **Click** on the new menu item "Deploy Webhook Button" and enter your site details/keys
8. **Enter** enter your site_id, webhook POST address, Netlify API Key, and User-Agent
9. **Read** the documentation to [get started](https://github.com/lukethacoder/wp-netlify-webhook-deploy)

---

## Where do I get the field data from?

### Webhook Build
1. **Visit** Netlify > Site-Name > Settings > Build & Deploy
2. **Create** A Build Hook (or use an existing hook)
3. **Copy** The Build Hook URL into the `Webhook Build URL` field

[![Webhook Build URL](https://github.com/lukethacoder/wp-netlify-webhook-deploy/blob/master/assets/screenshot-2.png)](https://github.com/lukethacoder/wp-netlify-webhook-deploy)

### Netlify site_id
1. **Visit** Netlify > Site-Name > Settings
2. **Copy** APP_ID and paste into the `Netlify site_id` field

[![Netlify Site Info](https://github.com/lukethacoder/wp-netlify-webhook-deploy/blob/master/assets/screenshot-3.png)](https://github.com/lukethacoder/wp-netlify-webhook-deploy)

### Netlify API Key
1. **Visit** Netlify > OAuth Applications > Personal Access Tokens
2. **Create** A Personal Access Token (or use an existing one)
3. **Copy** The token and paste into the `Netlify API Key` field

[![Netlify OAuth Applications](https://github.com/lukethacoder/wp-netlify-webhook-deploy/blob/master/assets/screenshot-1.png)](https://github.com/lukethacoder/wp-netlify-webhook-deploy)

### User-Agent Site Value
1. **Visit** Netlify > Site-Name > Settings
2. **Copy** The Site Name and paste into the `User-Agent Site Value` field
3. **Add** The site url in brackets to the `User-Agent Site Value` field
4. Your field should look similar to this `SiteNameNoSpaces (site-name-url.com)`

[![Netlify Site Info](https://github.com/lukethacoder/wp-netlify-webhook-deploy/blob/master/assets/screenshot-3.png)](https://github.com/lukethacoder/wp-netlify-webhook-deploy)

---

## Screenshots

Main Plugin page

[![Main Plugin Page](https://github.com/lukethacoder/wp-netlify-webhook-deploy/blob/master/assets/screenshot-1.png)](https://github.com/lukethacoder/wp-netlify-webhook-deploy)


---

## Links
* [Website](https://github.com/lukethacoder/wp-netlify-webhook-deploy)
* [Documentation](https://github.com/lukethacoder/wp-netlify-webhook-deploy)

---


## Changelog

#### 0.1.0
* Initial Release

View full changelog: [here](https://github.com/lukethacoder/deploy-webhook-button)