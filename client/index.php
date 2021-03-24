<?php

  // Setting a default endpoint
  $base_endpoint = "http://lesson-files/Examples/api-example/api";

  // Accessing the rows from our contacts API endpoint
  $rows = json_decode(file_get_contents("{$base_endpoint}/contacts"));

  // Getting our ID in case it's an edit or delete action
  $id = filter_input(INPUT_GET, 'id');

  // Grabbing the row so we can view the result (we have to decode this)
  $row = $id ? json_decode(file_get_contents("{$base_endpoint}/contacts/show/{$id}")) : null;

  // Getting our action so we know what POST action we're attempting
  $action = filter_input(INPUT_GET, 'action') ?? "create";

  // Our random quote from the chuckisms endpoint
  $random_quote = json_decode(file_get_contents("{$base_endpoint}/chuckisms"));

  /*
    POSTing is a bit more involved than just getting data...
  */
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    /*
      This will setup the http request object we want to send
      - header: these are the headers we want to send. This content type simulates a form
      - method: the HTTP request method we want to use to submit our form to the endpoint
      - content: this is the request body we want to send to the endpoint. This will be
        stored in the $_POST associative array.
    */
    $options = [
      "http" => [
        "header" => "Content-type: application/x-www-form-urlencoded",
        "method" => "POST",
        "content" => http_build_query($_POST)
      ]
    ];

    /*
      stream_context_create() is like when you click a link or submit a form.
      It opens a request to whatever endpoint you're attempting to access.
      While we have the request open, we can send commands to eventually get the
      response.

      Once we have the stream open, we can make the request to get our contents.
      We've already sent the headers and body info. Now we're just retrieving what
      ever has rendered at the other end as a response.

      This two stage process is necessary for us to get our endpoint data in PHP.
      In JavaScript we'd use either the fetch API or the Axios library to simplify
      this.
    */
    try {
      $context = stream_context_create($options);
      $response = file_get_contents("{$base_endpoint}/contacts/{$action}", false, $context);

      // If we get a valid response we'll redirect so we can see the change.
      if ($response) header("Location: {$_SERVER['PHP_SELF']}");
      exit();
    } catch (Exception $error) {
      var_dump($error->getMessage());
    }
  }

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BmbxuPwQa2lc/FVzBcNJ7UAyJxM6wuqIj61tLrc4wSX0szH/Ev+nYRRuWlolflfl" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.0/font/bootstrap-icons.css">

    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.bundle.min.js" integrity="sha384-b5kHyXgcpbZJO/tY9Ul7kGkf1S0CWuKcCD38l8YkeH8z8QjE0GmW1gYU5S9FOnJ0" crossorigin="anonymous"></script>

    <title>The Client</title>
  </head>

  <body>
    <div class="container">
      <div class="row">
        <div class="col">
          <header class="my-5 bg-light rounded p-5">
            <h1>The Client</h1>
            <hr>
            <p>
              <?= $random_quote->value ?>
            </p>
          </header>
        </div>
      </div>

      <div class="row">
        <div class="col">
          <form method="post" action="<?= $_SERVER['PHP_SELF'] ?><?= $action ? "?action={$action}" : null ?>">
            <input type="hidden" name="id" value="<?= $row->id ?? null ?>">

            <div class="row my-3">
              <div class="col-5">
                <div class="form-group">
                  <label for="fname">First Name:</label>
                  <input type="text" required name="fname" class="form-control" value="<?= $row->fname ?? null ?>">
                </div>
              </div>

              <div class="col-5">
                <div class="form-group">
                  <label for="lname">Last Name:</label>
                  <input type="text" required name="lname" class="form-control" value="<?= $row->lname ?? null ?>">
                </div>
              </div>

              <div class="col-2">
                <div class="form-group">
                  <label for="age">Age:</label>
                  <input type="number" name="age" class="form-control" min="18" max="130" value="<?= $row->url ?? null ?>">
                </div>
              </div>
            </div>

            <div class="row my-3">
              <div class="col">
                <div class="form-group">
                  <label for="email">Email:</label>
                  <input type="email" required name="email" class="form-control" value="<?= $row->email ?? null ?>">
                </div>
              </div>
            </div>

            <div class="row my-3">
              <div class="col">
                <div class="form-group">
                  <label for="url">Personal Page:</label>
                  <input type="url" name="url" class="form-control" value="<?= $row->url ?? null ?>">
                </div>
              </div>
            </div>

            <div class="row my-5">
              <div class="col">
                <div class="form-group">
                  <?php
                    $buttonText = "Create";
                    if ($action === "update") $buttonText = "Update";
                    if ($action === "delete") $buttonText = "Delete";
                  ?>
                  <button type="submit" class="btn btn-primary" onclick="<?= $action ? "return confirm('Are you sure?')" : null ?>">
                    <?= $buttonText ?>
                  </button>

                  <a href="<?= $_SERVER['PHP_SELF'] ?>" class="btn btn-danger">Reset</a>
                </div>
              </div>
            </div>
          </form>
        </div>

        <div class="col">
          <table class="table table-striped">
            <thead>
            
            </thead>
              <tr>
                <th>Name</th>
                <th>Age</th>
                <th>Email</th>
                <th>URL</th>
                <th></th>
              </tr>
            <tbody>
              <?php foreach ($rows as $row): ?>
                <tr>
                  <td><?= "{$row->fname} {$row->lname}" ?></td>
                  <td><?= $row->age ?></td>
                  <td>
                    <a href="mailto:<?= $row->email ?>"><?= $row->email ?></a>
                  </td>
                  <td>
                    <a href="<?= $row->url ?>" target="_blank"><?= $row->url ?></a>
                  </td>
                  <td>
                    <a href="?id=<?= $row->id ?>&action=update"><i class="bi bi-pencil-fill"></i></a>
                    &nbsp;/&nbsp;
                    <a href="?id=<?= $row->id ?>&action=delete"><i class="bi bi-trash-fill"></i></a>
                  </td>
                </tr>
              <?php endforeach ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </body>
</html>