<?php
    $host = "db";
    $port = "5432";
    $database = "example";
    $user = "localuser";
    $password = "cs4640LocalUser!";

    $dbHandle = pg_connect("host=$host port=$port dbname=$database user=$user password=$password");

    if ($dbHandle) {
        echo "Success connecting to database<br>\n";
    } else {
        die("An error occurred connecting to the database");
    }

    // Drop tables and sequences (that are created later)
    $res  = pg_query($dbHandle, "drop table if exists project_users cascade;");
    $res  = pg_query($dbHandle, "drop table if exists project_trips cascade;");
    $res  = pg_query($dbHandle, "drop table if exists project_entries cascade;");
    $res  = pg_query($dbHandle, "drop table if exists project_stats cascade;");
    $res  = pg_query($dbHandle, "drop table if exists project_bucketlist cascade;");

    $res  = pg_query($dbHandle, "drop sequence if exists project_users_seq;");
    $res  = pg_query($dbHandle, "drop sequence if exists project_trips_seq;");
    $res  = pg_query($dbHandle, "drop sequence if exists project_entries_seq;");

    // Create sequences
    $res  = pg_query($dbHandle, "create sequence project_users_seq;");
    $res  = pg_query($dbHandle, "create sequence project_trips_seq;");
    $res  = pg_query($dbHandle, "create sequence project_entries_seq;");

    // Create tables
    $res  = pg_query($dbHandle, "create table project_users (
            id  int primary key default nextval('project_users_seq'),
            name text not null,
            email text unique not null,
            password text not null,
            date_joined date default current_date
            );");

    $res  = pg_query($dbHandle, "create table project_trips (
            id  int primary key default nextval('project_trips_seq'),
            user_id int references project_users(id) on delete cascade,
            name text not null,
            location text not null,
            latitude double precision not null,
            longitude double precision not null,
            start_date date default current_date,
            end_date date,
            collaborators text[],
            notes text
            );");

    $res  = pg_query($dbHandle, "create table project_entries (
            id int primary key default nextval('project_entries_seq'),
            user_id int references project_users(id) on delete cascade,
            trip_id int references project_trips(id) on delete cascade,
            date date default current_date,
            title text not null,
            entry text not null,
            image_url text
            );");

    $res  = pg_query($dbHandle, "create table project_stats (
            user_id int references project_users(id) on delete cascade,
            num_trips int default 0,
            num_entries int default 0,
            num_bucketlist int default 0,
            num_visited int default 0,
            num_cities int default 0,
            num_countries int default 0,
            miles_traveled int default 0
            );");

    $res = pg_query($dbHandle, "create table project_bucketlist (
            user_id int references project_users(id) on delete cascade,
            location text,
            latitude double precision,
            longitude double precision,
            visited boolean default false
            );");

    echo "Done!";
