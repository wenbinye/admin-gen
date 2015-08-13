CREATE TABLE `customers` (
 `id` INTEGER PRIMARY KEY AUTOINCREMENT,
 `name` VARCHAR(100) NOT NULL ,
 `email` VARCHAR(100) NOT NULL ,
 `mobile` VARCHAR(100) NOT NULL,
 `memo` VARCHAR(1024)
);

CREATE TABLE science_books (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT,
  book_name varchar(100) NOT NULL,
  author varchar(100) NOT NULL,
  price int(10) NOT NULL,
  qty int(10) NOT NULL
);
