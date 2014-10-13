create database if not exists ifcRush;
use ifcRush;


DROP TABLE IF EXISTS BID;
DROP TABLE IF EXISTS REGISTRATION;
DROP TABLE IF EXISTS EVENT;
DROP TABLE IF EXISTS FRATERNITY;
DROP TABLE IF EXISTS RUSHEE;

CREATE TABLE RUSHEE(
	netID		varchar(6) not null,
	firstName	varchar(15) not null,
	lastName	varchar(15) not null,
	rushstat	varchar(3) not null,
	gradYear    int,
	school		varchar(8) not null,
	residence	varchar(10) not null,
	email		varchar(30) not null,
	PRIMARY KEY(netID)
) engine = InnoDB;

CREATE TABLE FRATERNITY(
	fullname varchar(15) not null,
	letters varchar(3) not null,
	rushchair varchar(15) not null,
	email varchar(30) not null,
	PRIMARY KEY(letters)
) engine = InnoDB;

CREATE TABLE EVENT(
	eventDate date not null,
	title varchar(30) not null,
	ID int not null auto_increment,
	fratID varchar(3) not null,
	PRIMARY KEY(ID),
	FOREIGN KEY(fratID) references FRATERNITY(letters)
) engine = InnoDB;

CREATE TABLE BID(
	bidstat int not null,
	RusheeID		varchar(6) not null,
	fratID varchar(3) not null,
	FOREIGN KEY (RusheeID) references RUSHEE(netID),
	FOREIGN KEY (fratID) references FRATERNITY(letters)
) engine = InnoDB;

CREATE TABLE REGISTRATION(
	RusheeID		varchar(6) not null,
	eventID int not null auto_increment,
	FOREIGN KEY (RusheeID) references RUSHEE(netID),
	FOREIGN KEY (eventID) references EVENT(ID)
) engine = InnoDB;