<?php namespace App\Console\Commands;

/**
 * https://willvincent.com/2016/04/12/easily-running-custom-scripts-in-a-bootstrapped-laravel-environment/
 *
 * Usage Example:
 * php artisan script <file_path> <args>
 *
 * <file_path> = path/filename.php of script to run relative to project root
 * <args> = Optional args to pass to script
 */

use Illuminate\Console\Command;

class Script extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'script
                            {filename : path/filename.php of script to run relative to project root}
                            {args?* : Optional args to pass to script}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs a script inside a bootstrapped laravel environment.';

    /**
     * Execute the console command.
     */
    public function handle(): mixed
    {
        // Make $args (if any) available to the file being included
        $args = $this->argument('args');

        // include the script file to run
        include $this->argument('filename');

        exit();
    }
}
