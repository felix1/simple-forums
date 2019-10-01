# Simple Forums

This will be a simple forum software built on CodeIgniter 4. It is as much a demo project as it is a tool
that I wanted for use on a couple of sites. It is being modelled after more minimal/modern forum software
like [NodeBB](https://community.nodebb.org/), the [site Statamic built](https://lodge.statamic.com/), 
and the like.

**NOTE: THIS WAS AN EXPERIMENT THAT DIED. DON'T FRET, THOUGH, SEE [THIS FORUM](https://github.com/lonnieezell/myth-forums) FOR ONE UNDER ACTIVE DEVELOPMENT BY ME.**
 
## Requirements

**CodeIgniter 4 must be previously downloaded**

This repo does NOT have all of the code needed inside of it currently. Since this is being developed
alongside CodeIgniter 4, itself, the system folder is not provided. Instead, the system currently 
is setup to find it in a `CodeIgniter4` folder. You'll need to adjust the routes in `application/Config/Paths.php`
to match the location of your CI4 `system` folder.
 
## Installation

To install follow these steps: 

1. clone the repo (git clone git@github.com:lonnieezell/simple-forums.git)
2. Adjust the path to the system folder as noted above.
3. Create a `.env` file and add your database credentials (modify for your db, obviously): 

```
# Database
database.default.database = ciforum
database.default.username = root
database.default.password = root
database.default.DBDriver = MySQLi
```

If you want to use all of the dev tools that I am, you'll need to follow a couple of extra steps:

1. run `composer install`, which will install [Faker](https://github.com/fzaninotto/Faker). This is required
    to run the database seeder and populate the database with sample data.
2. Download my code generation cli tools, [Vulcan](https://github.com/lonnieezell/vulcan) and adjust the 
    path for the PSR4 namespace in `application/Config/Autoload.php` to point to the right location.
