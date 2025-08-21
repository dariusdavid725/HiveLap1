// Variabile pentru simulare și grid
let grid = [];
let cols, rows;
let cellSize = 20;
let startCell, endCell;
let openSet = [];
let closedSet = [];
let path = [];
let simulationStarted = false;

function setup() {
  let canvas = createCanvas(400, 400);
  canvas.parent('p5-container');
  cols = floor(width / cellSize);
  rows = floor(height / cellSize);
  
  // Inițializare grid
  for (let i = 0; i < cols; i++) {
    grid[i] = [];
    for (let j = 0; j < rows; j++) {
      grid[i][j] = new Cell(i, j);
    }
  }
  
  startCell = grid[0][0];
  endCell = grid[cols - 1][rows - 1];
  startCell.wall = false;
  endCell.wall = false;
  
  // Generare aleatorie de obstacole (poți modifica ulterior în funcție de layout)
  for (let i = 0; i < cols; i++) {
    for (let j = 0; j < rows; j++) {
      if (random(1) < 0.3 && grid[i][j] !== startCell && grid[i][j] !== endCell) {
        grid[i][j].wall = true;
      }
    }
  }
  
  // Adăugare vecini pentru fiecare celulă
  for (let i = 0; i < cols; i++) {
    for (let j = 0; j < rows; j++) {
      grid[i][j].addNeighbors(grid, cols, rows);
    }
  }
  
  openSet.push(startCell);
  noLoop();
}

function draw() {
  background(220);
  
  if (simulationStarted) {
    if (openSet.length > 0) {
      let winner = 0;
      for (let i = 0; i < openSet.length; i++) {
        if (openSet[i].f < openSet[winner].f) {
          winner = i;
        }
      }
      
      let current = openSet[winner];
      
      if (current === endCell) {
        noLoop();
        simulationStarted = false;
        console.log("Simulare finalizată!");
      }
      
      removeFromArray(openSet, current);
      closedSet.push(current);
      
      let neighbors = current.neighbors;
      for (let neighbor of neighbors) {
        if (!closedSet.includes(neighbor) && !neighbor.wall) {
          let tempG = current.g + 1;
          let newPath = false;
          if (openSet.includes(neighbor)) {
            if (tempG < neighbor.g) {
              neighbor.g = tempG;
              newPath = true;
            }
          } else {
            neighbor.g = tempG;
            newPath = true;
            openSet.push(neighbor);
          }
          if (newPath) {
            neighbor.h = heuristic(neighbor, endCell);
            neighbor.f = neighbor.g + neighbor.h;
            neighbor.previous = current;
          }
        }
      }
      
      path = [];
      let temp = current;
      path.push(temp);
      while (temp.previous) {
        path.push(temp.previous);
        temp = temp.previous;
      }
    } else {
      noLoop();
      simulationStarted = false;
      console.log("Nu există soluție!");
    }
  }
  
  // Desenarea grid-ului
  for (let i = 0; i < cols; i++) {
    for (let j = 0; j < rows; j++) {
      grid[i][j].show(color(255));
    }
  }
  
  // Colorare celule pentru openSet și closedSet
  for (let cell of closedSet) {
    cell.show(color(255, 0, 0));
  }
  
  for (let cell of openSet) {
    cell.show(color(0, 255, 0));
  }
  
  // Desenarea traseului găsit
  noFill();
  stroke(0, 0, 255);
  strokeWeight(cellSize / 2);
  beginShape();
  for (let cell of path) {
    vertex(cell.i * cellSize + cellSize / 2, cell.j * cellSize + cellSize / 2);
  }
  endShape();
}

function heuristic(a, b) {
  return dist(a.i, a.j, b.i, b.j);
}

function removeFromArray(arr, elt) {
  for (let i = arr.length - 1; i >= 0; i--) {
    if (arr[i] == elt) {
      arr.splice(i, 1);
    }
  }
}

class Cell {
  constructor(i, j) {
    this.i = i;
    this.j = j;
    this.f = 0;
    this.g = 0;
    this.h = 0;
    this.wall = false;
    // Setare aleatorie a obstacolului (poți dezactiva această linie dacă vei folosi layout-ul salvat)
    if (random(1) < 0.2) {
      this.wall = true;
    }
    this.neighbors = [];
    this.previous = undefined;
  }
  
  addNeighbors(grid, cols, rows) {
    let i = this.i;
    let j = this.j;
    if (i < cols - 1) this.neighbors.push(grid[i + 1][j]);
    if (i > 0) this.neighbors.push(grid[i - 1][j]);
    if (j < rows - 1) this.neighbors.push(grid[i][j + 1]);
    if (j > 0) this.neighbors.push(grid[i][j - 1]);
    if (i > 0 && j > 0) this.neighbors.push(grid[i - 1][j - 1]);
    if (i < cols - 1 && j > 0) this.neighbors.push(grid[i + 1][j - 1]);
    if (i > 0 && j < rows - 1) this.neighbors.push(grid[i - 1][j + 1]);
    if (i < cols - 1 && j < rows - 1) this.neighbors.push(grid[i + 1][j + 1]);
  }
  
  show(col) {
    fill(col);
    if (this.wall) {
      fill(0);
    }
    noStroke();
    rect(this.i * cellSize, this.j * cellSize, cellSize - 1, cellSize - 1);
  }
}

// Buton pentru pornirea simulării
document.getElementById("start-simulation").addEventListener("click", function() {
  simulationStarted = true;
  loop();
});

// Configurare drag & drop pentru elementele din zona de design
interact('.draggable').draggable({
  inertia: true,
  modifiers: [
    interact.modifiers.restrictRect({
      restriction: '#design-area',
      endOnly: true
    })
  ],
  listeners: {
    move (event) {
      let target = event.target;
      let x = (parseFloat(target.getAttribute('data-x')) || 0) + event.dx;
      let y = (parseFloat(target.getAttribute('data-y')) || 0) + event.dy;
      target.style.transform = 'translate(' + x + 'px, ' + y + 'px)';
      target.setAttribute('data-x', x);
      target.setAttribute('data-y', y);
    }
  }
});

// Funcție pentru colectarea datelor layout-ului
function getLayoutData() {
  let elements = document.querySelectorAll('#design-area .draggable');
  let layout = [];
  elements.forEach(el => {
    let rect = el.getBoundingClientRect();
    let parentRect = document.getElementById('design-area').getBoundingClientRect();
    layout.push({
      type: el.getAttribute('data-type'),
      x: rect.left - parentRect.left,
      y: rect.top - parentRect.top,
      width: rect.width,
      height: rect.height
    });
  });
  return layout;
}

// Salvare layout prin AJAX către php/save_layout.php
document.getElementById("save-layout").addEventListener("click", function() {
  let layoutData = getLayoutData();
  fetch('php/save_layout.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ layout: layoutData })
  })
  .then(response => response.json())
  .then(data => {
    if(data.success) {
      alert("Layout salvat cu succes!");
    } else {
      alert("Eroare la salvare: " + data.error);
    }
  })
  .catch(err => {
    console.error("Eroare: ", err);
  });
});
