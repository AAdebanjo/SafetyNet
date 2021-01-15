<!doctype html>

<html lang="en">

  <head>
    <meta charset="utf-8" />
    <title>Box Model Demo</title>
    <style>
      body {
        background: #eeeeee;
        text-align: center;
        font-family: Arial, Helvetica, sans-serif;
      }
      .content {
        background: #ffffff;
        font-size: 2em;
      }
      .inner {
        padding: 5em;
        border: 5px solid #808080;
        margin: 5em;
        background: #eeeeee;
      }
      .outer {
        display: inline-block;
        background: #ffffff;
      }
    </style>
  </head>

  <body>
    <div class="outer">
      <div class="inner">
        <span class="content">
          <h1>CONGRATULATIONS!</h1>
          <p>As of the end of this month, you are one of the TOP 5 Thanksgivers!</p>
          <br>
          <p>To celebrate this occassion, you have been given an additional 20 Thanks. Continue the good work, fellow contributor!</p>
          <br>
          <p>Click on the button below to access your user profile.</p>
          <button onclick="window.location.href='index.php'">Click me</button>
        </span>
      </div>
    </div>
  </body>

</html>