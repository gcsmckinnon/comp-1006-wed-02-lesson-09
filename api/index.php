<?php

  /*
    A simple function to respond with the correct headers
    and status code. Will convert the response into JSON.
  */
  function response ($code, $message) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($message);
  }

  /*
    The connect function will be availabe to any of our
    subsequent included files. This means that to interact
    with our connection, we simply call "dbo()" anywhere.
  */
  require_once("connect.php");

  /*
    A ReSTful API requires us to be able to understand the
    request path provided, as it will determine the key parts
    needed to build the proper response.
  */
  /*
    $_SERVER['PHP_SELF']:
      - returns the complete working path (for this file), relative to the root of our application
      - ie:  /Examples/api-example/index.php
    
    dirname():
      - returns just the directory path the file is being executed in
      - ie: /Examples/api-example

    $_SERVER['REQUEST_URI']:
      - returns the requested path (everything after the domain)
      - ie: /Examples/api-example/contacts/
  */
  /*
    The resolved path is the intersect of the index.php file's location
    and the requested path. The result will be the remaining portion of
    the path.
      - ie: /contacts/
  */
  $resolved_path = str_replace(dirname($_SERVER['PHP_SELF']), '', $_SERVER['REQUEST_URI']);

  /*
    The "parts" are the various pieces of our requested path,
    sorted by concern:
    - root: represents the root of our path (this will likely be empty)
    - resource: this is the resource you're attempting to access
    - action: this is the function in the resource's controller you want to execute
    - params: this is the remaining values in the path string that
              will be handled as passed parameters (similar to query key/values)
  */
  $parts = explode("/", $resolved_path);
  $root = $parts[0];
  $resource = $parts[1];
  $action = $parts[2] ?? null;
  $params = isset($parts[3]) ? array_slice($parts, 3, count($parts)) : [];

  /*
    This is the HTTP method used to make the request
    - ie: GET, POST, PUT, DELETE, HEAD, PATCH
  */
  $request_method = $_SERVER["REQUEST_METHOD"];

  /*
    The logic below first attempts to see if a resource
    has been defined (ie: "contacts").
  */
  if ($resource) {
    // Then we dynamically require the controller file for the resource
    require_once("{$resource}/controller.php");

    // Next we conditionally call the action requested
    switch ($action) {
      // GET actions
      case "show":
      case "search":
        // If the HTTP method requested for the action is wrong, we respond with a 404
        if ($request_method !== "GET") {
          return response(404, ["statusMessage" => "Not Found"]);
        }
        break;
      // POST actions
      case "create":
      case "update":
      case "delete":
        // If the HTTP method requested for the action is wrong, we respond with a 404
        if ($request_method !== "POST") {
          return response(404, ["statusMessage" => "Not Found"]);
        }
        break;
      // Default GET action when no action has been requested
      default:
        // If the HTTP method requested for the action is wrong, we respond with a 404
        if ($request_method !== "GET") {
          return response(404, ["statusMessage" => "Not Found"]);
        }

        // Our default action will always be "index"
        $action = "index";
        break;
    }  
    
    /*
      The $action value is a string, not a function itself.
      In order to call the function using the string, we use
      the call_user_func_array() library function provided by
      PHP. This takes two arguments
        1) The function you want to call (as a string)
        2) The arguments you want to pass (as an array)
    */
    return call_user_func_array($action, $params);
  }