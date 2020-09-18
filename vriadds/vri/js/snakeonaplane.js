//snake on a plane


function loadGame()
{
    width = $("#game").width() / 15;
    width = width.toFixed(0) * 15;
    jVRI("#game").html( '<canvas id="canvas" width="'+width+'" height="450"></canvas>' );
    snake();
}

function snake()
{
  //Canvas stuff
  var canvas = $("#canvas")[0];
  var ctx = canvas.getContext("2d");
  var w = $("#canvas").width();
  var h = $("#canvas").height();

  //Lets save the cell width in a variable for easy control
  var cw = 15;
  var d;
  var food;
  var score;

  //Lets create the snake now
  var snake_array; //an array of cells to make up the snake

  function init()
  {
    d = "right"; //default direction
    create_snake();
    create_food(); //Now we can see the food particle

    score = 0;

    if(typeof game_loop != "undefined") clearInterval(game_loop);
    game_loop = setInterval(paint, 60);
  }
  init();

  function create_snake()
  {
    var length = 5; //Length of the snake
    snake_array = []; //Empty array to start with
    for(var i = length-1; i>=0; i--)
    {
      //This will create a horizontal snake starting from the top left
      snake_array.push({x: i, y:0});
    }
  }

  //Lets create the food now
  function create_food()
  {
    food = {
      x: Math.round(Math.random()*(w-cw)/cw), 
      y: Math.round(Math.random()*(h-cw)/cw), 
    };
    //This will create a cell with x/y between 0-44
    //Because there are 45(450/10) positions accross the rows and columns
  }

  //Lets paint the snake now
  function paint()
  {
    //To avoid the snake trail we need to paint the BG on every frame
    ctx.fillStyle = "white";
    ctx.fillRect(0, 0, w, h);
    ctx.strokeStyle = "black";
    ctx.strokeRect(0, 0, w, h);

    //Pop out the tail cell and place it infront of the head cell
    var nx = snake_array[0].x;
    var ny = snake_array[0].y;

    //Lets add proper direction based movement now
    if     (d == "right") nx++;
    else if(d == "left")  nx--;
    else if(d == "up")    ny--;
    else if(d == "down")  ny++;

    //This will restart the game if the snake hits the wall
    //Now if the head of the snake bumps into its body, the game will restart
    if(nx == -1 || nx == w/cw || ny == -1 || ny == h/cw || check_collision(nx, ny, snake_array))
    {
      //restart game
      init();
      return;
    }

    //Lets write the code to make the snake eat the food
    //If the new head position matches with that of the food,
    //Create a new head instead of moving the tail
    if(nx == food.x && ny == food.y)
    {
      var tail = {x: nx, y: ny};
      score++;

      create_food();
    }
    else
    {
      var tail = snake_array.pop(); //pops out the last cell
      tail.x = nx; tail.y = ny;
    }
    //The snake can now eat the food.

    snake_array.unshift(tail); //puts back the tail as the first cell

    for(var i = 0; i < snake_array.length; i++)
    {
      var c = snake_array[i];
      //Lets paint 10px wide cells
      paint_cell(c.x, c.y);
    }

    //Lets paint the food
    paint_cell(food.x, food.y,"orange");
    //Lets paint the score
    var score_text = "Score: " + score;
        ctx.fillStyle = "black";
    ctx.fillText(score_text, 5, h-5);
  }

  //Lets first create a generic function to paint cells
  function paint_cell(x, y,color="#a94442")
  {
    ctx.fillStyle = color; //"blue";
    ctx.fillRect(x*cw, y*cw, cw, cw);
    ctx.strokeStyle = "white";
    ctx.strokeRect(x*cw, y*cw, cw, cw);
  }

  function check_collision(x, y, array)
  {
    //This function will check if the provided x/y coordinates exist
    //in an array of cells or not
    for(var i = 0; i < array.length; i++)
    {
      if(array[i].x == x && array[i].y == y)
       return true;
    }
    return false;
  }

  //Lets add the keyboard controls now
  $(document).keydown(function(e){
    var key = e.which;
    //We will add another clause to prevent reverse gear
    if(key == "37" && d != "right") d = "left";
    else if(key == "38" && d != "down") d = "up";
    else if(key == "39" && d != "left") d = "right";
    else if(key == "40" && d != "up") d = "down";
    //The snake is now keyboard controllable
  });

    $( "#game" ).click(function(e) {
        r = Math.floor( (Math.random() * 4) );

        //document.title = e.clientX + " :: " + e.screenX;

        if( r==0 && d!="up" ) d = "down";
        if( r==1 && d!="down") d = "up";
        if( r==2 && d!="right") d = "left";
        if( r==3 && d!="left") d = "right";
    });
}
