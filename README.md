# Initial-implementation  
At this point we open a mail in gmail server that is called : emoodlesmessage@gmail.com this mail box will used for students to send a message to courses by
emoodlesmessage+"name of universty or number"+"number of course" +@gmail.com. example: emoodlesmessage+ariel56748@gmail.com -> mail for course number : 56748 at Ariel University. 


"name of universty or number"+"number of course" +"."+"number of the forum" +@gmail.com. example: emoodlesmessage+13.6@gmail.com -> mail for course number : 13 and number of forum 6.

# About the script :
These scripts will grab a folder of your choosing from gmail and insert it into a MySQL table. 
These scripts will grab a folder of your choosing from gmail and insert it into a MySQL table. The information that we enter to the DB is : 'id', 'sender', 'subject', 'date' , 'message' and 'recive'.
The script is currently manually and in the future will be automaticlly.

Improvement we sould add to the script:  
* delete the mail from the inbox as we add it to the DB
* add a random number to every course mail for security purpose
* check for every email that we get if the send mail is address of student that is register for the spesific course
# sources :  
https://github.com/stefobark/mail
