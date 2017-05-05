<?php

namespace App\Console\Commands;

use App\User;
use App\Model\Store;
use App\Model\Role;
use App\Model\UserDetail;
use Illuminate\Console\Command;
use Validator;

class InstallApp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run TokoBaru Installation';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('This setup will create the default store and admin user');

        sleep(3);

        // setup default
        $storeName = 'Toko Baru';
        $userName = 'Admin';
        $userEmail = 'admin@app.com';
        $userPassword = 'thepassword';

        $valid = false;

        while (!$valid) {
            // input
            $storeName = $this->ask('Enter Default Store Name:', $storeName);
            $userName = $this->ask('Enter User Name:', $userName);
            $userEmail = $this->ask('Enter User Email:', $userEmail);
            $userPassword = $this->ask('Enter User Password:', $userPassword);

            $validator = Validator::make([
                'store' => $storeName,
                'name' => $userName,
                'email' => $userEmail,
                'password' => $userPassword
            ], [
                'store' => 'required|min:3|max:100',
                'name' => 'required|min:3|max:50',
                'email' => 'required|max:255|email|unique:users,email',
                'password' => 'required|min:7'
            ]);

            if (!$validator->fails()) {
                $valid = true;
            } else {
                foreach ($validator->errors()->all() as $errorMessage) {
                    $this->error($errorMessage);
                }
            }
        }

        // confirmation
        $confirmed = $this->confirm("Everything's OK? Do you wish to continue?");

        if (!$confirmed) {
            $this->info('Aborted');
            return false;
        }

        // create entity
        $store = Store::create([
            'name'          => $storeName,
            'address'       => '',
            'phone_num'     => '',
            'fax_num'       => '',
            'tax_id'        => '0000000000',
            'status'        => 'STATUS.ACTIVE',
            'is_default'    => 'YESNOSELECT.YES',
            'remarks'       => ''
        ]);

        if ($store) {
            $this->info('Store "'.$store->name.'" is created.');
        }

        $user = User::create([
            'name' => $userName,
            'email' => $userEmail,
            'password' => bcrypt($userPassword),
        ]);

        if ($user) {
            $this->info('User "'.$user->name.'" is created.');
        }

        $this->info('Associate "'.$user->name.'" with "'.$store->name.'".');

        $user->store()->associate($store->id);
        $user->save();

        $this->info('Setup "'.$user->name.'" as Admin.');

        $userDetail = new UserDetail();
        $userDetail->allow_login = true;
        $userDetail->type = 'USERTYPE.A';
        $user->userDetail()->save($userDetail);

        $user->attachRole(Role::where('name', 'admin')->first());

        $this->info('Done!');
    }
}