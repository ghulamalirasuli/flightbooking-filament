<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * This method is called when you run `php artisan migrate`.
     */
    public function up(): void
    {
        // Use Schema::table() to modify an existing table
        Schema::table('accounts', function (Blueprint $table) {
            $table->index('account_name');
        });
        Schema::table('account_category', function (Blueprint $table) {
            $table->index('accounts_category');
        });

        Schema::table('account_registration', function (Blueprint $table) {
            $table->index('fullname');
        });

         Schema::table('branches', function (Blueprint $table) {
            $table->index('branch_name');
            $table->index('branch_code');
        });

          Schema::table('currency', function (Blueprint $table) {
            $table->index('currency_name');
            $table->index('currency_code');
        });

         Schema::table('expense_type', function (Blueprint $table) {
            $table->index('type');
        });

         Schema::table('flightinfo', function (Blueprint $table) {
            $table->index('conn_id');
            $table->index('airlines');
            $table->index('flightno');
            $table->index('f_from');
            $table->index('f_to');
            // Indexing a date column
            $table->index('depart_time');
            $table->index('arrival_time');
            
            $table->index('fullname');
        });

        Schema::table('group_flight', function (Blueprint $table) {
            $table->index('airlines');
            $table->index('flightno');
            $table->index('from_f');
            $table->index('to_f');
            // Indexing a date column
            $table->index('depart_time');
            $table->index('arrival_time');
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->index('subject');
            $table->index('category');
        });

        Schema::table('our_service', function (Blueprint $table) {
            $table->index('title');
        });

        Schema::table('passenger_info', function (Blueprint $table) {
            $table->index('conn_id');
            $table->index('flightno');
            $table->index('first_name');
            $table->index('last_name');
            // Indexing a date column
            $table->index('passportno');
        });

        Schema::table('payment_gateway', function (Blueprint $table) {
           $table->index('api_name');
            $table->index('account_number');
        });

        Schema::table('providers', function (Blueprint $table) {
           $table->index('auth_type');
        });

        Schema::table('pt_flights_airlines', function (Blueprint $table) {
           $table->index('name');
        });

        Schema::table('pt_flights_airports', function (Blueprint $table) {
           $table->index('code');
             $table->index('name');
            $table->index('cityCode');
            $table->index('cityName');
             $table->index('countryName');
            $table->index('countryCode');
        });

     Schema::table('pt_flights_countries', function (Blueprint $table) {
             $table->index('name');
            $table->index('nicename');
            $table->index('iso3');
        });

     Schema::table('services', function (Blueprint $table) {
             $table->index('service_name');
            $table->index('price');
        });


/*
        Schema::table('flights', function (Blueprint $table) {
            // Indexing common search columns
            $table->index('flight_number');
            $table->index('departure_airport');
            $table->index('arrival_airport');
            
            // Indexing a date column
            $table->index('departure_time');
            
            // Compound index: crucial for finding flights efficiently
            // e.g., WHERE departure_airport = 'JFK' AND departure_time > NOW()
            $table->index(['departure_airport', 'departure_time']);
        });

        Schema::table('bookings', function (Blueprint $table) {
            // Indexing foreign keys
            $table->index('flight_id');
            $table->index('user_id');
            
            // Indexing a date column
            $table->index('booking_date');
        });

        // Example: Adding indexes to a new table (Passengers)
        Schema::table('passengers', function (Blueprint $table) {
            $table->index('booking_id');
            $table->index('last_name');
        });

        // Add more Schema::table() calls here for your other tables
        // like 'airlines', 'seats', etc.
        */
    }
        

  
    public function down(): void
    {
        /*
        Schema::table('flights', function (Blueprint $table) {
            // The name of the index is tablename_columnname_index
            $table->dropIndex('flights_flight_number_index');
            $table->dropIndex('flights_departure_airport_index');
            $table->dropIndex('flights_arrival_airport_index');
            $table->dropIndex('flights_departure_time_index');
            $table->dropIndex('flights_departure_airport_departure_time_index'); // Drop compound index
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('bookings_flight_id_index');
            $table->dropIndex('bookings_user_id_index');
            $table->dropIndex('bookings_booking_date_index');
        });
        
        Schema::table('passengers', function (Blueprint $table) {
            $table->dropIndex('passengers_booking_id_index');
            $table->dropIndex('passengers_last_name_index');
        });
        */
             Schema::table('accounts', function (Blueprint $table) {
            $table->dropIndex('accounts_account_name_index');
        });
        Schema::table('account_category', function (Blueprint $table) {
            $table->dropIndex('account_category_accounts_category_index');
        });

        Schema::table('account_registration', function (Blueprint $table) {
            $table->dropIndex('account_registration_fullname_index');
        });

         Schema::table('branches', function (Blueprint $table) {
            $table->dropIndex('branches_branch_name_index');
            $table->dropIndex('branches_branch_code_index');
        });

          Schema::table('currency', function (Blueprint $table) {
            $table->dropIndex('currency_currency_name_index');
            $table->dropIndex('currency_currency_code_index');
        });

         Schema::table('expense_type', function (Blueprint $table) {
            $table->dropIndex('expense_type_type_index');
        });

         Schema::table('flightinfo', function (Blueprint $table) {
            $table->dropIndex('flightinfo_conn_id_index');
            $table->dropIndex('flightinfo_airlines_index');
            $table->dropIndex('flightinfo_flightno_index');
            $table->dropIndex('flightinfo_f_from_index');
            $table->dropIndex('flightinfo_f_to_index');
            // Indexing a date column
            $table->dropIndex('flightinfo_depart_time_index');
            $table->dropIndex('flightinfo_arrival_time_index');
            
            $table->dropIndex('flightinfo_fullname_index');
        });

        Schema::table('group_flight', function (Blueprint $table) {
            $table->dropIndex('group_flight_airlines_index');
            $table->dropIndex('group_flight_flightno_index');
            $table->dropIndex('group_flight_from_f_index');
            $table->dropIndex('group_flight_to_f_index');
            // Indexing a date column
            $table->dropIndex('group_flight_depart_time_index');
            $table->dropIndex('group_flight_arrival_time_index');
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex('messages_subject_index');
            $table->dropIndex('messages_category_index');
        });

        Schema::table('our_service', function (Blueprint $table) {
            $table->dropIndex('our_service_title_index');
        });

        Schema::table('passenger_info', function (Blueprint $table) {
            $table->dropIndex('passenger_info_conn_id_index');
            $table->dropIndex('passenger_info_flightno_index');
            $table->dropIndex('passenger_info_first_name_index');
            $table->dropIndex('passenger_info_last_name_index');
            // Indexing a date column
            $table->dropIndex('passenger_info_passportno_index');
        });

        Schema::table('payment_gateway', function (Blueprint $table) {
           $table->dropIndex('payment_gateway_api_name_index');
            $table->dropIndex('payment_gateway_account_number_index');
        });

        Schema::table('providers', function (Blueprint $table) {
           $table->dropIndex('providers_auth_type_index');
        });

        Schema::table('pt_flights_airlines', function (Blueprint $table) {
           $table->dropIndex('pt_flights_airlines_name_index');
        });

        Schema::table('pt_flights_airports', function (Blueprint $table) {
           $table->dropIndex('pt_flights_airports_code_index');
             $table->dropIndex('pt_flights_airports_name_index');
            $table->dropIndex('t_flights_airports_citycode_index');
            $table->dropIndex('pt_flights_airports_cityname_index');
             $table->dropIndex('pt_flights_airports_countryname_index');
            $table->dropIndex('pt_flights_airports_countrycode_index');
        });

     Schema::table('pt_flights_countries', function (Blueprint $table) {
             $table->dropIndex('pt_flights_countries_name_index');
            $table->dropIndex('pt_flights_countries_nicename_index');
            $table->dropIndex('pt_flights_countries_iso3_index');
        });

     Schema::table('services', function (Blueprint $table) {
             $table->dropIndex('services_service_name_index');
            $table->dropIndex('services_price_index');
        });
    }
};
