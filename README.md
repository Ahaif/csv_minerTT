# Prerequisite:
  - PHP, Composer, Symphony 6.4, SQLite
# testing:
  - git clone project
  - run composer update
  - create Database & migrate schema, run following commands
      - php bin/console make:migration
      - php bin/console doctrine:migrations:migrate
  - seed csv file tests in test_folder
  - Run the script to update and modify entries in DB accordingly
      - php bin/console app:import-csv folder_test
  - The Video Below shows the state of the database before and after runing the script

[Screencast from 15.05.2024 02.26.17.webm](https://github.com/Ahaif/csv_minerTT/assets/81704547/f9bbca5f-726f-4bc6-9f5f-7a331aed13c1)
