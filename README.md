# SPIS

Hello fellow developers!

### Route Lists

| Need Token  | Method      | URL                    | Description                            |
| ----------- | ----------- | ---------------------- | -------------------------------------- |
| NO          | POST        | /api/auth/login        | Authentication user                    |
| YES         | POST        | /api/auth/logout       | Logout user                            |
| YES         | GET         | /api/scholars          | Return list of scholars                |
| YES         | POST        | /api/scholars         | Register scholars                       |
| YES         | GET         | /api/scholars/{id}     | Return specific of scholars            |


For SMS, need to register in Twilio (free trial is okay) and set it to .env file the following

TWILIO_SID={SID provided by Twilio}

TWILIO_TOKEN={Token provided by Twilio}

TWILIO_FROM={Number provided by Twilio}
