## Commission Calculation with Varying Business Rules

This is a sample application developed as part of an assignment. The assignment was to develop an application
where Business and Private clients can deposit and withdraw funds to/from their accounts. A commission fee is
charged based on action (deposit/withdrawal), user type (Business/Private), as well as several business rules like
commission-free withdrawal facility up to a certain amount for a specified maximum number of times in a week, and so on.

The detailed specification can be found in the Task.md file.

## Notes about the solution

- Laravel framework has been used primarily to utilize the Dependency Injection container.
- The entry point to the application is the `app/Scripts/calculate_commission.php` script.
The application flow can be traced starting from there.
- The business rules for calculating commission has been designed in a pattern to encapsulate
each rule into a single Rule class, so as to minimize changes required to add/remove/reorder the rules.
- In-memory array has been used as data store (`app\Repositories\TransactionRepository`).
- Week start day has been set to `Monday` as specified, inside the `app\Providers\AppServiceProvider::boot()` method.
To ease datetime related operations, `Carbon` has been used.
- Commission fees have been rounded up to currency's decimal places as specified. PHP doesn't have a built-in
solution for rounding up a floating number (irrespective of whether the next digit is >= 5). A solution has been used that is
outlined in `Utils\Math::roundUp()`.
- To prevent degrading performance due to calling the currency exchange rate API everytime it's needed, the API response has been
cached during the entire lifecycle of the application.
- Object-Oriented Design Principles like _Single Responsibility Principle_, _Open-Closed Principle_,
_Dependency Inversion Principle_ etc. have been maintained as much as possible. 
- It took around 8 hours to fully complete the application with all optimizations.

## Setting Up

1. Make sure `composer` is installed on the system.
2. `cd` to this project directory.
3. Run `composer update` to update the dependencies.
4. Make the `storage` directory writable by everyone using this command:

    `$ sudo chmod -R 777 storage`

## Running the Application

1. Place `input.csv` file at a convenient location and note the absolute path.
2. `cd` to this project directory.
3. Run the following command to process the input and show the output
(replace `<absolute-path-of-input.csv>` with the absolute path to `input.csv` file):

    `$ php artisan script app/Scripts/calculate_commission.php <absolute-path-of-input.csv>`

## Running Tests

1. `cd` to this project directory.
2. Run the following command:

    `$ php artisan test`
