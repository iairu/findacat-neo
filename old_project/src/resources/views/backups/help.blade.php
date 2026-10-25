@extends('layouts.app')

@section('ext_css')
<style>
body {
        background-image: url("images/cat-1045782-3.jpg");
        background-position: center;
        background-repeat: no-repeat;
        background-size: cover;
        background-attachment: fixed;
    }
.navbar-default  {
        background: white;
}
</style>
@endsection

@section('title',trans('backup.index_title'))

@section('content')
<h3 class="page-header">{{ trans('backup.index_title') }}</h3>
<div class="row">
    <div class="col-md-8">
            <h3>CSV Backup Import</h3>
            <p>Order and format of columns in CSV import has to be kept as in export.</p>
            <p>- As of 2024-03-19 column order has to be as follows: id, full_name, gender_id, sire_id, dam_id, dob, titles_before_name, titles_after_name, ems_color, breed, chip_number, genetic_tests, breeding_station, country_code, alternative_name, print_name_r1, print_name_r2, dod, original_reg_num, last_reg_num, reg_num_2, reg_num_3, notes, breeder, current_owner, country_of_origin, country, ownership_notes, personal_info, genetic_tests_file, photo, vet_confirmation, doo
            <p>- The "id" column works as a UUID meaning that you can input any unique IDs you desire.</p>
            <p>- Existing UUIDs won't be replaced.</p>
            <p>As CSV integrity can't be guaranteed, here is the recommended usage:</p>
            <p>- Create a backup using GZ first guaranteeing valid integrity</p>
            <p>- The first backup can be on an empty database, this is recommended so you can easily restore the original state.</p>
            <p>- Use CSV import feature afterwards.</p>
            <p>- If integrity breaks restore given GZ backup.</p>
            <p>- Necessary: You must include id "1" with full_name set to "no origin" or similar for correct family trees and inbreeding calculation.</p>
            <p>- Dates of birth set to 1111-11-11 will have a note stating "Not set"</p>
            <p>---</p>
            <p>Note that the following database queries have to be executed for every user that should have access to backup functionality or cat registration: use database_name; update users set is_admin = TRUE where id = 1; Database name by default is homestead for the Docker container.</p>
            <p>---</p>
            <p>Poradie a formát stĺpcov v CSV importe musí byť ponechaný rovnaký ako v exporte</p>
            <p>- Od dňa 2024-03-19 má byť poradie stĺpcov nasledovné: id, full_name, gender_id, sire_id, dam_id, dob, titles_before_name, titles_after_name, ems_color, breed, chip_number, genetic_tests, breeding_station, country_code, alternative_name, print_name_r1, print_name_r2, dod, original_reg_num, last_reg_num, reg_num_2, reg_num_3, notes, breeder, current_owner, country_of_origin, country, ownership_notes, personal_info, genetic_tests_file, photo, vet_confirmation, doo
            <p>- Stĺpec "id" funguje ako UUID, čo znamená že je možné použiť akékoľvek unikátne ID aké chcete.</p>
            <p>- Existujúce UUIDs nebudú prepísané.</p>
            <p>Keďže integrita CSV nemôže byť zaručená, je odporúčané nasledovné:</p>
            <p>- Vytvorte najprv GZ zálohu zaručujúcu integritu databázy.</p>
            <p>- Prvá záloha môže byť na prázdnej databáze, čo aj odporúčame keďže následne môžete jednoducho vrátiť predvolený stav.</p>
            <p>- Využite CSV import funkcionalitu.</p>
            <p>- Ak je problém s integritou obnovte GZ zálohu.</p>
            <p>- Potrebné: Treba obsiahnuť id "1" s full_name nastavené na "no origin" alebo podobne pre správne rodostromy a počítanie inbreedingu.</p>
            <p>- Dátum narodenia nastavený na 1111-11-11 bude mať poznámku "Not set"</p>
            <p>---</p>
            <p>Nasledovné príkazy nad databázou musia byť použité pre každého používateľa, ktorý by mal mať prístup k funkcionalite zálohovania alebo k registrácií mačiek: use database_name; update users set is_admin = TRUE where id = 1; Database name by default is homestead for the Docker container.</p>
</p>
    </div>
</div>
@endsection
