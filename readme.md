# RescueGroups SDK Builder

This is a library that is used to automatically generate the queries in the [salernolabs/rescuegroups](https://github.com/ericsalerno/rescuegroups) unofficial HTTP API PHP SDK.

Unless you plan to develop the library, you can probably ignore that this even exists.

## Running A Build

This tool is designed to be run from the main directory of the source application in the vendor folder after being delivered from composer. What does that mean?

### 1. Include this library in your composer.json

    "require-dev": {
        ...
        "salernolabs/rescuegroups-sdk-builder": "dev-master"
    },

### 2. Composer update

Self-explanatory

### 3. Run it

In the root directory where your composer.json is, run it:

    ~/rescuegroups> php vendor/salernolabs/rescuegroups-sdk-builder/bin/generate.php
    