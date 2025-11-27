# certificateelement_fieldfrommoddata

## Overview
`certificateelement_fieldfrommoddata` is a custom element for the Moodle Certificate plugin.  
It allows you to display values from a **Database activity (mod_data)** in a certificate, based on an user’s entry.

---

## Features
- Fetches field values from a `mod_data` instance.
- Displays selected field on generated certificates.
- Supports only one single entry to `mod_data` per user. Falls back to placeholder text if multiple or no entries are found.

---

## Requirements
- Moodle version: 5.x or later
- Certificate plugin installed and configured

---

## Installation
1. Download the plugin package and place the folder in:
`local/certificate/element/fieldfrommoddata`
2. Log in as an administrator and go to:
**Site administration → Notifications**  
Complete the installation process.

---

## Usage
1. Create or edit a certificate template.
2. Add the **Field from mod_data** element.
3. Configure:
- Course module ID of the database activity
- Name of the field to display must be unique for this database activity.
4. Save and generate certificates.

---

## Configuration Options
- **Course Module ID**: The `cmid` of the database activity.
- **Field Name**: Name of the unique field to include from the database activity.

---

## Changelog
### v1.0.0
- Initial release
- Basic functionality to fetch and display mod_data fields

---

## License
This plugin is licensed under the GNU GPL v3.

---