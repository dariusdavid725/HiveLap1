<?php
ob_start();
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: ../Polihack1/php/register.php");
  exit;
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>HiveLap - Dashboard</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Orbitron:wght@500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/p5.js/1.6.0/p5.min.js"></script>
  <style>
    :root {
      --bg: #0A0A0A;
      --panel: #151515;
      --accent: #00BFFF;
      --text: #E0E0E0;
      --border: #333;
      --highlight: #00F0FF;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Inter', sans-serif;
      background-color: var(--bg);
      color: var(--text);
      display: flex;
      height: 100vh;
      overflow: hidden;
    }

    .sidebar {
      background: linear-gradient(180deg, var(--panel) 0%, #0F0F0F 100%);
      width: 260px;
      padding: 2rem 1rem;
      display: flex;
      flex-direction: column;
      align-items: center;
      border-right: 1px solid var(--border);
    }

    .sidebar .brand {
      font-family: 'Orbitron', sans-serif;
      font-size: 1.8rem;
      color: var(--accent);
      margin-bottom: 2rem;
      display: flex;
      align-items: center;
      gap: 1rem;
    }

    .sidebar .brand i {
      font-size: 2rem;
    }

    .sidebar nav ul {
      list-style: none;
      width: 100%;
    }

    .sidebar nav ul li {
      margin-bottom: 1.5rem;
    }

    .sidebar nav ul li a {
      display: flex;
      align-items: center;
      gap: 1rem;
      text-decoration: none;
      color: var(--text);
      padding: 0.75rem 1rem;
      border-radius: 0.5rem;
      transition: all 0.3s;
      font-weight: 500;
    }

    .sidebar nav ul li a:hover {
      background-color: var(--accent);
      color: #fff;
    }

    .sidebar nav ul li a i {
      width: 20px;
      text-align: center;
    }

    .main-content {
      flex: 1;
      display: flex;
      flex-direction: column;
      overflow-y: auto;
    }

    .header {
      padding: 1.5rem 2rem;
      background-color: var(--panel);
      border-bottom: 1px solid var(--border);
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .header h1 {
      font-family: 'Orbitron', sans-serif;
      font-size: 1.8rem;
      color: var(--accent);
    }

    .content {
      padding: 2rem;
      flex: 1;
    }

    .hero {
      max-width: 800px;
      margin: 0 auto;
      text-align: center;
      padding: 3rem 0;
    }

    .hero h1 {
      font-family: 'Orbitron', sans-serif;
      font-size: 2.5rem;
      color: var(--accent);
      margin-bottom: 1.5rem;
    }

    .hero p {
      font-size: 1.1rem;
      line-height: 1.6;
      color: var(--text);
      margin-bottom: 2.5rem;
      opacity: 0.9;
    }

    .buttons {
      display: flex;
      justify-content: center;
      gap: 1.5rem;
      max-width: 1000px;
      margin: 0 auto;
      flex-wrap: nowrap;
    }

    .btn {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.75rem;
      padding: 1rem 1.5rem;
      background-color: var(--panel);
      color: var(--text);
      text-decoration: none;
      border-radius: 0.5rem;
      border: 1px solid var(--border);
      transition: all 0.3s;
      font-weight: 500;
      min-width: 200px;
    }

    .btn:hover {
      background-color: var(--accent);
      color: #fff;
      transform: translateY(-2px);
    }

    .btn i {
      font-size: 1.2rem;
    }

    #canvas-container {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: -1;
      opacity: 0.1;
    }

    .modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.8);
      z-index: 1000;
    }

    .modal-content {
      position: relative;
      background-color: var(--panel);
      margin: 5% auto;
      padding: 2rem;
      width: 80%;
      max-width: 800px;
      border-radius: 1rem;
      border: 1px solid var(--border);
    }

    .close-btn {
      position: absolute;
      top: 1rem;
      right: 1rem;
      color: var(--text);
      font-size: 1.5rem;
      cursor: pointer;
      transition: color 0.3s;
    }

    .close-btn:hover {
      color: var(--accent);
    }

    #simulation-canvas-container {
      width: 100%;
      height: 500px;
      background-color: var(--bg);
      border-radius: 0.5rem;
      overflow: hidden;
    }
  </style>
</head>
<body>
  <div class="sidebar">
    <div class="brand">
      <i class="fa-solid fa-warehouse"></i>
      <span>HiveLap</span>
    </div>
    <nav>
      <ul>
        <li><a href="index.php"><i class="fa-solid fa-chart-line"></i> Dashboard</a></li>
        <li><a href="php/layouts.php"><i class="fa-solid fa-layer-group"></i> Layout-uri</a></li>
        <li><a href="prezentare.html"><i class="fa-solid fa-play"></i> Prezentare</a></li>
        <li><a href="simulare.php"><i class="fa-solid fa-robot"></i> Simulare</a></li>
        <li><a href="#"><i class="fa-solid fa-user"></i> Contul meu</a></li>
        <li><a href="php/logout.php"><i class="fa-solid fa-sign-out-alt"></i> Logout</a></li>
      </ul>
    </nav>
  </div>

  <div class="main-content">
    <header class="header">
      <h1>Dashboard</h1>
    </header>
    
    <div class="content">
      <section class="hero">
        <h1>Bine ai venit în HiveLap</h1>
        <p>Platforma ta pentru gestionarea și simularea depozitelor automate. Explorează funcționalitățile noastre inovatoare și optimizează-ți operațiunile.</p>
        <div class="buttons">
          <a href="#" class="btn"><i class="fa-solid fa-user"></i> Contul meu</a>
          <a href="php/layouts.php" class="btn"><i class="fa-solid fa-layer-group"></i> Layout-uri</a>
          <a href="simulare.php" class="btn"><i class="fa-solid fa-robot"></i> Simulare</a>
          <a href="prezentare.html" class="btn"><i class="fa-solid fa-play"></i> Prezentare</a>
        </div>
      </section>
    </div>
  </div>

  <div id="canvas-container"></div>
  
  <div id="simulation-modal" class="modal">
    <div class="modal-content">
      <span class="close-btn">&times;</span>
      <div id="simulation-canvas-container"></div>
    </div>
  </div>

  <script>
    /* ==== Animația de fundal ==== */
    let bgParticles = [];
    let numBgParticles = 100;

    function setup() {
      let canvas = createCanvas(windowWidth, windowHeight);
      canvas.parent('canvas-container');
      for (let i = 0; i < numBgParticles; i++) {
        bgParticles.push(new Particle());
      }
      noStroke();
    }

    function draw() {
      background(20, 20, 40, 30);
      for (let p of bgParticles) {
        p.update();
        p.show();
      }
    }

    class Particle {
      constructor() {
        this.pos = createVector(random(width), random(height));
        this.vel = createVector(random(-1, 1), random(-1, 1));
        this.size = random(3, 8);
        this.alpha = random(150, 255);
      }

      update() {
        this.pos.add(this.vel);
        if (this.pos.x < 0 || this.pos.x > width) this.vel.x *= -1;
        if (this.pos.y < 0 || this.pos.y > height) this.vel.y *= -1;
      }

      show() {
        fill(255, 255, 255, this.alpha);
        ellipse(this.pos.x, this.pos.y, this.size);
      }
    }

    function windowResized() {
      resizeCanvas(windowWidth, windowHeight);
    }
    
    /* ==== Setup pentru simularea interactivă în modal ==== */
    let simSketch = function(p) {
      let robots = [];
      let numRobots = 5;
      let gridCols = 20;
      let gridRows = 20;
      let cellSize = 20;
      
      p.setup = function() {
        let simCanvas = p.createCanvas(gridCols * cellSize, gridRows * cellSize);
        simCanvas.parent('simulation-canvas-container');
        for(let i = 0; i < numRobots; i++){
          robots.push(new Robot(p.random(gridCols), p.random(gridRows)));
        }
      }
      
      p.draw = function() {
        p.background(50);
        // Desenare grid
        p.stroke(80);
        for(let i = 0; i <= gridCols; i++){
          p.line(i * cellSize, 0, i * cellSize, gridRows * cellSize);
        }
        for(let j = 0; j <= gridRows; j++){
          p.line(0, j * cellSize, gridCols * cellSize, j * cellSize);
        }
        // Actualizare și desenare roboți
        for(let robot of robots){
          robot.move();
          robot.display();
        }
      }
      
      class Robot {
        constructor(col, row) {
          this.col = col;
          this.row = row;
          this.targetCol = p.floor(p.random(gridCols));
          this.targetRow = p.floor(p.random(gridRows));
        }
        move() {
          if(this.col < this.targetCol) this.col++;
          else if(this.col > this.targetCol) this.col--;
          if(this.row < this.targetRow) this.row++;
          else if(this.row > this.targetRow) this.row--;
          
          if(this.col === this.targetCol && this.row === this.targetRow){
            this.targetCol = p.floor(p.random(gridCols));
            this.targetRow = p.floor(p.random(gridRows));
          }
        }
        display() {
          p.fill(0, 255, 0);
          p.noStroke();
          p.ellipse(this.col * cellSize + cellSize/2, this.row * cellSize + cellSize/2, cellSize * 0.8);
        }
      }
    }
    
    let simInstance;
    /* ==== Gestionarea modalului ==== */
    document.getElementById('simulate-btn').addEventListener('click', function(e) {
      e.preventDefault();
      document.getElementById('simulation-modal').style.display = 'block';
      if(!simInstance){
        simInstance = new p5(simSketch);
      }
    });
    
    document.getElementsByClassName('close-btn')[0].addEventListener('click', function(){
      document.getElementById('simulation-modal').style.display = 'none';
    });
    
    window.addEventListener('click', function(e){
      let modal = document.getElementById('simulation-modal');
      if(e.target == modal){
        modal.style.display = 'none';
      }
    });
  </script>
</body>
</html>