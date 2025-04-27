<!--
SPDX-FileCopyrightText: 2024 Framasoft <https://framasoft.org>
SPDX-FileContributor: Val Jossic <val@framasoft.org>

SPDX-License-Identifier: AGPL-3.0-only
-->

# Ownership Transfer

A Nextcloud app that allows admins to transfer data from one user to another.

![transfer menu](./screenshots/ownership_transfer.png)

## Installation

Clone this repo in the app directory of your Nextcloud environment.

Install the dependencies and build the frontend files:

```
composer install
npm install
npm run dev
```

Finally, enable the app in the Apps section in Nextcloud.

## Usage

The app provides a GUI in the admin settings section to choose a folder to transfer.

Currently supported apps:

 - Files: all the files or a folder
 - Calendar: all the calendars or only one
 - Contacts: all the contacts or an address book

## Contribute 

Contributions are welcome! Check the [issue list](https://framagit.org/framasoft/nextcloud/ownershiptransfer/-/issues) to find some issue to fix or report a problem.

You can also contribute to translating this app via [weblate](https://weblate.framasoft.org/projects/nextcloud/ownership-transfer/)
