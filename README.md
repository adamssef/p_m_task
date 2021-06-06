<!-- GETTING STARTED -->
## Getting Started

### Prerequisities
 To make it work you need to have MySQL db installed and run doctrine migrations.
  
   ```
    php bin/console doctrine:migrations:migrate
  ```
### TESTS

Tests are here to check if the database is corect. It uses PhpUnit. To run all of them and examine the results enter the command
  ```
   phpunit
  ```

### SYMFONY COMMANDS

1. Filtering and importing data from MySQL db:
   ```
   php bin/console app:import-json-data-to-db

  
2.Shows the table with product info together with some performance information
   ```
    php bin/console app:count-products-per-category
   ```

