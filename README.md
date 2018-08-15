### Detail module information

1. Namespace >> **modules/enterprise_customer**
2. Zip Archive source >> 
    https://github.com/aalfiann/reSlim-modules-enterprise_customer/archive/master.zip

### How to Integrate this module into reSlim?

1. Download zip then upload to reSlim server to the **modules/**
2. Extract zip then you will get new folder like **reSlim-modules-enterprise_customer-master**
3. Rename foldername **reSlim-modules-enterprise_customer-master** to **enterprise_customer**
4. Done

### How to Integrate this module into reSlim with Packager?

1. Make AJAX GET request to >>
    http://**{yourdomain.com}**/api/packager/install/zip/safely/**{yourusername}**/**{yourtoken}**/?lang=en&source=**{zip archive source}**&namespace=**{modul namespace}**

### How to integrate this module into database?
This module is require integration to the current database.

1. Make AJAX GET request to >>
    http://**{yourdomain.com}**/api/enterprise_customer/install/**{yourusername}**/**{yourtoken}**

### Security Tips
After successful integration database, you must remove the **install** and **uninstall** router.  
Just make some edit in the **enterprisecustomer.router.php** file manually.

### Requirements
- This module is require [Enterprise](https://github.com/aalfiann/reslim-modules-enterprise) module installed on reSlim.