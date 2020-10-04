# [E7twa](e7twa22.rg) Coding Task

E7twa coding task is about creating simple web application backend API only for an admin to view a list of volunteers registered in
the system.

I used PHP language and [Codeigniter4](http://codeigniter.com/) framework to do this API.

There are 3 endpoits:
- `http://localhost:8080/API/register` To register a volunteer POST
- `http://localhost:8080/API/volunteers/API_KEY` To view list of volunteers(accesible by admin only) GET
- `http://localhost:8080/API/login` To login ethier as admin or volunteer, in case if its volunteer it will display its information, if admin create a JWT token.


## Requirements
You must download XAMPP, MAMP or similar app

###In order to test this API follow these steps:

1. Open XAMPP and run the Apache and MySQL server 
2. Open this [URL](http://localhost/phpmyadmin/index.php) and import the sql file named `tables.sql` in the repository
3. Write in the terminal `php spark serve`
4. test it in postman using the three endpoints above
