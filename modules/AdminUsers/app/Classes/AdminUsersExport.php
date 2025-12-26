<?php

namespace Modules\AdminUsers\Classes;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use DB;

class AdminUsersExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return DB::table('users')->select("id", "fullname", "role", "email", "plan_id", "expiration_date", "timezone", "last_login", "changed", "created")->get();
    }
    
    /**
     * Write code on Method
     *
     * @return response()
     */
    public function headings(): array
    {
        return ["ID", "Full Name", "Email", "Role" ,"Plan ID", "Expiration Date", "Timezone", "Last login", "Changed", "Created"];
    }
}
