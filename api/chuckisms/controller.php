<?php

  function index () {
    /*
      file_get_contents() allows you to create a request to any
      file, anywhere. This can be used to create API requests.

      json_decode() converts a JSON string response into a usable
      PHP object.
    */
    $response = json_decode(file_get_contents("https://api.chucknorris.io/jokes/random"));
    return response(200, $response);
  }