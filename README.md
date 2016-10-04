# Google PubSub for Laravel & Lumen

## TODO


* Tne subscribe endpoint has to return to the right status codes because amazon and good handle responses differently
* So the subscribe endpoint has to call handle on the message
* Add retries() method to the message that can be overrirden per message to use the  - use a retries environment variable so all messages don't have to implement it'
* Switch the package name to PubSub or Paperboy or Bugle
* Repair the config - move it out from under queue
* Test it end to end again inside the testscontroller
* What is the test coverage?
* Write the readme for setting up pub sub from scratch using gcloud
* write sns driver


Illuminate\Contracts\PubSub\ShouldPublish
  https://github.com/laravel/framework/blob/7d116dc5a008e69c97f864af79ac46ab6a8d5895/src/Illuminate/Contracts/Broadcasting/ShouldBroadcast.php

