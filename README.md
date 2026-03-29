# Online Watch Store FYP Pack (Fixed Version)

## What was fixed
- Register and login links now work even if you rename the project folder.
- Database connection now tries common database names automatically.
- Product card image overlap issue is fixed.
- Product catalog was replaced with real Malaysia model names and prices.
- Local offline demo images are bundled inside `assets/images/` so the homepage still looks correct without internet hotlink issues.

## Suggested setup
1. Extract this folder into `C:\xampp\htdocs`.
2. You may keep the folder name as `fyp_watch_store_top` or rename it.
3. Open phpMyAdmin and import `database/watch_store.sql`.
4. Recommended database name: `online_watch_shop`.
5. Visit the project using your folder name, for example:
   - `http://localhost/fyp_watch_store_top/`
   - or `http://localhost/Online_Watch_Shop/`

## Default accounts
- Superadmin: `superadmin@watchstore.com` / `Admin123!`
- Admin: `admin@watchstore.com` / `Admin123!`
- Customer: `customer@watchstore.com` / `Customer123!`

## Product price sources used in this pack
- Tissot PRX collection Malaysia
- CASIO Malaysia GA-2100-1A1 product page
- Seiko Boutique Malaysia SSA457J1 product page
- Citizen Malaysia Tsuyosa NJ0200-50E listing

## Notes
The included product images are local demo images bundled with the project. I used local images instead of hotlinking brand product photos so your local demo will stay stable during presentation.
