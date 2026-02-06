
DROP TABLE poi;
CREATE TABLE poi
(
    id          INT AUTO_INCREMENT PRIMARY KEY,
    poi     TEXT,
    betriebstyp int,
    strasse     TEXT,
    plz         varchar(10),
    web         varchar(255),
    lat         float,
    lon         float
);





