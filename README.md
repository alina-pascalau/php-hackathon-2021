# PHP Hackathon
This document has the purpose of summarizing the main functionalities your application managed to achieve from a technical perspective. Feel free to extend this template to meet your needs and also choose any approach you want for documenting your solution.

## Problem statement
*Congratulations, you have been chosen to handle the new client that has just signed up with us.  You are part of the software engineering team that has to build a solution for the new client’s business.
Now let’s see what this business is about: the client’s idea is to build a health center platform (the building is already there) that allows the booking of sport programmes (pilates, kangoo jumps), from here referred to simply as programmes. The main difference from her competitors is that she wants to make them accessible through other applications that already have a user base, such as maybe Facebook, Strava, Suunto or any custom application that wants to encourage their users to practice sport. This means they need to be able to integrate our client’s product into their own.
The team has decided that the best solution would be a REST API that could be integrated by those other platforms and that the application does not need a dedicated frontend (no html, css, yeeey!). After an initial discussion with the client, you know that the main responsibility of the API is to allow users to register to an existing programme and allow admins to create and delete programmes.
When creating programmes, admins need to provide a time interval (starting date and time and ending date and time), a maximum number of allowed participants (users that have registered to the programme) and a room in which the programme will take place.
Programmes need to be assigned a room within the health center. Each room can facilitate one or more programme types. The list of rooms and programme types can be fixed, with no possibility to add rooms or new types in the system. The api does not need to support CRUD operations on them.
All the programmes in the health center need to fully fit inside the daily schedule. This means that the same room cannot be used at the same time for separate programmes (a.k.a two programmes cannot use the same room at the same time). Also the same user cannot register to more than one programme in the same time interval (if kangoo jumps takes place from 10 to 12, she cannot participate in pilates from 11 to 13) even if the programmes are in different rooms. You also need to make sure that a user does not register to programmes that exceed the number of allowed maximum users.
Authentication is not an issue. It’s not required for users, as they can be registered into the system only with the (valid!) CNP. A list of admins can be hardcoded in the system and each can have a random string token that they would need to send as a request header in order for the application to know that specific request was made by an admin and the api was not abused by a bad actor. (for the purpose of this exercise, we won’t focus on security, but be aware this is a bad solution, do not try in production!)
You have estimated it takes 4 weeks to build this solution. You have 2 days. Good luck!*

## Technical documentation
### Data and Domain model
In this section, please describe the main entities you managed to identify, the relationships between them and how you mapped them in the database.

    Entities are:
    Programme, User, Room, Building
    Relationships are:
    Programme has a relation ManyToMAny with User. There is a join table in the database (programme_user)
    Programme has a relation ManyToOne with Room. There is a programme_id field in the room table
    Room has a relation ManyToOne with Building. There is a building_id in room table

### Application architecture
In this section, please provide a brief overview of the design of your application and highlight the main components and the interaction between them.

    The main components are the Entities, Repositories and the controller. The controller handles the requests and outputs the result.
###  Implementation
##### Functionalities
For each of the following functionalities, please tick the box if you implemented it and describe its input and output in your application:

    [x] Brew coffee 
        With milk :)
    [x] Create programme
        It uses the POST method and the required params in json format are : name, maxParticipants, startdate, enddate and room.
        The user token should be in the request header.
        The output is in json format and contains a status and some data.
    [X] Delete programme 
        The request uses the DELETE method and the required param is the id of the programme.
        The user token should be in the request header.
        The output is in json format and contains a status and some data.
    [X] Book a programme 
        The request uses the POST method and tthe required params are: name of programme and user's CNP.
        The output is in json format and contains a status and some data.

##### Business rules
Please highlight all the validations and mechanisms you identified as necessary in order to avoid inconsistent states and apply the business logic in your application.

    Create programme: Validates all the request parameters (required and date format validation) and the token. It also checks if the room has been booked for that time.
    Delete programme: Checks if the request param id is a valid id of a programme and checks for the token. 
    Book a programme: Validates all the required parameters. Checks if the programme is fully booked. Checks if the user has another programme in the same time.
    Register a user: Validates all the required parameters. Validates the CNP.

##### 3rd party libraries (if applicable)
Please give a brief review of the 3rd party libraries you used and how/ why you've integrated them into your project.

##### Environment
Please fill in the following table with the technologies you used in order to work at your application. Feel free to add more rows if you want us to know about anything else you used.
| Name | Choice |
| ------ | ------ |
| Operating system (OS) | Ubuntu 20.04 |
| Database  | MySQL 8.0|
| Web server| Apache |
| PHP | 7.4.3 |
| IDE | Apache Netbeans |

### Testing
In this section, please list the steps and/ or tools you've used in order to test the behaviour of your solution.

    I tested using Postman. I made requests that covered all the possibilities.

## Feedback
In this section, please let us know what is your opinion about this experience and how we can improve it:

1. Have you ever been involved in a similar experience? If so, how was this one different? 
        I haven't been involved in a similar experience. 
2. Do you think this type of selection process is suitable for you? 
        Yes.Working on a project is better that answering questions. 
3. What's your opinion about the complexity of the requirements? 
        Good complexity. I could tell the Problem statement was very well thought out. 
4. What did you enjoy the most? 
        The entire project. 
5. What was the most challenging part of this anti hackathon? 
        Working in the weekend :)  
6. Do you think the time limit was suitable for the requirements? 
        Yes, it was suitable. 
7. Did you find the resources you were sent on your email useful? 
        Yes.  
8. Is there anything you would like to improve to your current implementation? 

9. What would you change regarding this anti hackathon? 

