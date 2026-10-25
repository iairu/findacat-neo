<div align="center">
  <h1>🐈 Find A Cat - Cat Pedigree Management with Inbreeding</h1>
  <p>Ondrej Špánik (iairu) &copy; 2024</p>
</div>

## Overview

Find A Cat is a web application based on Laravel to help people find and manage cats, cat pedigrees, and calculate inbreeding of cat pairs to determine if mating is a good idea. Please read my thesis to get a better idea about the functionality and intentions behind this application. My thesis includes a guide on how to set up an admin account by flagging it as such in the database after registration.

See for yourself: [findacat.eu](https://findacat.eu)

## Web Application

The web application is a Laravel-based system designed to help cat breeders and enthusiasts manage and track cat pedigrees. 
This application uses English, Slovak, and supports multiple languages.
It offers features such as:

- Cat registration and pedigree tracking
- Breed and color (EMS) management
- User authentication and authorization
- Pedigree visualization

## Thesis

The thesis document provides an in-depth look at the research, development process, and technical decisions made during the creation of the Find A Cat application. It covers topics such as:

- Background on cat breeding and pedigree management
- System architecture and design decisions
- Implementation details and challenges
- User experience considerations
- Future improvements and potential expansions

The thesis documents can be found in the `thesis` directory.

## Directory Structure

- `src/`: Contains the Laravel application source code
- `thesis/`: Contains the thesis document and related materials
- `db_demo/`: Includes demo database import files to be used in the web app UI, such as the EMS values CSV

## Contributing

This project is primarily for educational purposes. However, if you'd like to contribute or have suggestions, please open an issue or submit a pull request.

## License

Find A Cat project (`src` folder) is based on Silsilah (https://github.com/nafiesl/silsilah) and open-sourced software licensed under the [MIT license](src/LICENSE) with the exception of my thesis (`thesis` folder) and database demo files used to demonstrate import capabilities of the web interface (`db_demo` folder).

Thesis and database files are © 2024 Ondrej Špánik (iairu). All rights reserved. Redistribution without permission is prohibited; feel free to link to the GitHub repository instead. Personal use is fine, commercial use is not granted.
