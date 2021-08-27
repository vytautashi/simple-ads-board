# simple-ads-board
Very simple advertisement board in pure PHP (without framework) and MYSQL.

**Features:**
- Cached advertisements in directory: [/ad/](/ad/)
- Account system (login/register)
- Advertisement admin panel (delete, update, post)
- Security (salted passwords, CSRF tokens)

## Getting Started

### Prerequisites
Things you need to install.
- [PHP](https://www.php.net/downloads.php) (tested on PHP 8.0.7)
- [MYSQL](https://dev.mysql.com/downloads/)

### Installation
1. Clone repository using command `git clone https://github.com/vytautashi/simple-ads-board.git`
2. Add project files to your server
3. Create database `ads_board` in mysql database
4. Import sql file [/inc/ads_board.sql](/inc/ads_board.sql) in MYSQL database `ads_board`
5. Update MYSQL connection credentials file [/inc/mysql_conn.php](/inc/mysql_conn.php)
6. Access website via your server url (for example: [http://localhost/](http://localhost/))

## Screenshots
Admin panel
![screenshot admin panel](/inc/screenshot1.png)