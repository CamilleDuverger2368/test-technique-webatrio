# test-technique-webatrio

## lancement du projet

lancer les commandes suivantes, sans oublier de créer un .env et, potentiellement, un .env.local : 

--> composer install
--> symfony server:start

## auto-evaluation

Les actions suivantes ont été réalisées dans l'odre énoncé :

--> mise en place de l'ORM
--> création des entités
--> créations des différents endpoints
--> implémentation de chaque endpoint en commencant par la création d'un utilisateur
--> implémentation du endpoint de création d'un travail
--> difficultés à retrouver le bon système de jointure de table pour faire l'action via doctrine dans le temps imparti, par conséquent, changement de technique
--> bug rencontré et identifié de SF 7 avec la cascade de serialization, retrogradation du package `symfony/var-exporter` à la version 7.0.4
--> questionnements sur le endpoint de liste des utilisateurs par ordre alphabétique avec leur emploi actuel : ne lister que les utilisateurs actuellement employé ? lister tous les utilisateurs et préciser s'ils ne sont pas actuellement employés ?
--> implémentation du endpoint de liste des employés présents comme passés d'une entreprise
--> implémentation du endpoint de listing d'emplois entre deux dates pour un utilisateur spécifique

Je suis certaine qu'il existe un système de jointure de table qui permet de faire plus proprement et en utilisant moins de ressources que ce que j'ai codé pour récupérer les données des endpoints 3, 4 et 5 de l'exercice, mais j'ai préféré aller au plus court pour rester dans le temps imparti, quitte à devoir refactoriser le code plus tard quand le temps le permettra.
N'ayant plus le temps de générer une API DOC avec un swagger, je termine ce readme avec les informations nécessaires à l'utilisation de cette API.


# API

Les endpoints suivants nécessitent d'être précédés par la base de votre URL.

## /api/user/register

Create a new user

Méthode : POST
Mandatories parameters : 
--> name : user's name (string format)
--> firstName : user's first name (string format)
--> birthdate : user's birthdate (date format)
Returns : success = null / error = 400 and error's message

## /api/user/{id}/add/job

Add job to user

Méthode : POST
Mandatories parameters : 
--> id : user's id (int format)
--> position : user's position in job (string format)
--> entreprise : job's entreprise (string format)
--> startingDate : job's start (date format)
Non mandatories parameters : 
--> endingDate : job's end (date format)
Returns : success = "user has a new experience" / error = 400 and error's message

## /api/user/

List all users and their actual jobs

Méthode : GET
Returns : success = list of users and their jobs / error = 400 and error's message

## /api/user/entreprise/{name}

List all employees of an entreprise

Méthode : GET
Mandatories parameters : 
--> name : entreprise's name (string format)
Returns : success = list of employees of this entreprise / error = 400 and error's message

## /api/user/{id}/get/jobs?from=&to=

List all user's jobs between two dates

Méthode : GET
Mandatories parameters : 
--> id : user's id (int format)
--> from : begin of the search (date format)
--> to : end of the search (date format)
Returns : success = array of jobs / error = 400 and error's message