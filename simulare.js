// simulare.js - Codul simulării depozitului cu p5.js (include A* și Hivemind)

// Variabile globale pentru simulare
let grid = [];        // grila depozitului (matrice de celule)
let rows, cols;       // dimensiunile grilei (număr de rânduri și coloane)
let cellSize = 40;    // dimensiunea (în pixeli) a unei celule din grilă
let robots = [];      // lista roboților din simulare
let tasks = [];       // coada de sarcini (pozițiile "shelf" de procesat)
let gateTarget = null; // coordonata destinației de tip "gate" (se va folosi primul gate)
let layoutData;       // obiectul layout încărcat din baza de date

// Clase pentru elementele simulării
class Cell {
  constructor(x, y) {
    this.x = x;
    this.y = y;
    this.type = 'empty';    // tipul celulei: 'empty', 'dock', 'gate', 'shelf', 'obstacle'
    this.walkable = true;   // dacă celula este disponibilă pentru navigație (false pentru obstacle/shelf)
    // A* algorithm fields
    this.f = 0;
    this.g = 0;
    this.h = 0;
    this.parent = null;
  }
}

class Robot {
  constructor(x, y) {
    this.x = x;
    this.y = y;
    this.path = [];       // drumul curent (o listă de coordonate de parcurs)
    this.state = 'idle';  // starea: 'idle' (inactiv), 'toShelf' (în drum spre un raft), 'toGate' (în drum spre poartă)
  }
}

// Funcție de euristică pentru A* (distanta Manhattan între două celule)
function heuristic(a, b) {
  return Math.abs(a.x - b.x) + Math.abs(a.y - b.y);
}

// Implementarea algoritmului A* pentru a găsi drumul optim între două celule din grilă
function findPath(startCell, goalCell) {
  // Resetăm valorile A* în celule
  for (let r = 0; r < rows; r++) {
    for (let c = 0; c < cols; c++) {
      let cell = grid[r][c];
      cell.f = 0;
      cell.g = 0;
      cell.h = 0;
      cell.parent = null;
    }
  }
  // Inițializăm listele openSet și closedSet
  let openSet = [];
  let closedSet = new Set();
  // Adăugăm celula de start în openSet
  startCell.g = 0;
  startCell.h = heuristic(startCell, goalCell);
  startCell.f = startCell.h;
  openSet.push(startCell);

  // Algoritmul A* (parcurgere până găsim drumul sau epuizăm opțiunile)
  while (openSet.length > 0) {
    // Selectăm nodul cu costul f cel mai mic din openSet
    let currentIndex = 0;
    for (let i = 1; i < openSet.length; i++) {
      if (openSet[i].f < openSet[currentIndex].f) {
        currentIndex = i;
      }
    }
    let current = openSet[currentIndex];

    // Verificăm dacă am ajuns la destinație
    if (current === goalCell) {
      // Reconstruim drumul prin a urmări părinții de la goal la start
      let path = [];
      let temp = current;
      path.push({x: temp.x, y: temp.y});
      while (temp.parent) {
        temp = temp.parent;
        path.push({x: temp.x, y: temp.y});
      }
      path.reverse(); // inversăm pentru a obține ordinea de la start la goal
      return path;
    }

    // Mutăm nodul curent din openSet în closedSet
    openSet.splice(currentIndex, 1);
    closedSet.add(current);

    // Verificăm vecinii ortogonali (sus, jos, stânga, dreapta)
    let neighbors = [
      {x: current.x + 1, y: current.y},
      {x: current.x - 1, y: current.y},
      {x: current.x, y: current.y + 1},
      {x: current.x, y: current.y - 1}
    ];
    for (let nb of neighbors) {
      // Verificăm limitele grilei
      if (nb.x < 0 || nb.x >= cols || nb.y < 0 || nb.y >= rows) {
        continue;
      }
      let neighbor = grid[nb.y][nb.x];
      if (closedSet.has(neighbor)) {
        // Dacă vecinul e deja evaluat, trecem la următorul
        continue;
      }
      // Dacă vecinul nu este walkable și nici destinația, îl ignorăm
      if (!neighbor.walkable && neighbor !== goalCell) {
        continue;
      }
      // Costul g tentativ al vecinului dacă trecem prin current
      let tentativeG = current.g + 1;
      let inOpenSet = openSet.includes(neighbor);
      if (!inOpenSet) {
        // Adăugăm vecinul în openSet dacă nu e deja acolo
        openSet.push(neighbor);
      } else if (tentativeG >= neighbor.g) {
        // Dacă este deja în openSet dar noul drum nu este mai bun, ignorăm
        continue;
      }
      // Actualizăm valorile pentru celula vecină (un drum mai bun a fost găsit)
      neighbor.parent = current;
      neighbor.g = tentativeG;
      neighbor.h = heuristic(neighbor, goalCell);
      neighbor.f = neighbor.g + neighbor.h;
    }
  }
  // Dacă openSet devine gol și nu am returnat, nu există drum (returnăm listă goală)
  return [];
}

// Funcție utilitară pentru a extrage coordonatele (x,y) dintr-o intrare de poziție (poate fi obiect sau array)
function getCoord(pos) {
  if (Array.isArray(pos)) {
    return {x: pos[0], y: pos[1]};
  } else {
    return {x: pos.x, y: pos.y};
  }
}

// Încarcă datele de layout din baza de date înainte de setup (preload asigură că avem datele disponibile)
function preload() {
  // Se face o cerere către scriptul PHP care returnează layout-ul ca JSON
  layoutData = loadJSON('..Polihack1/php/preluare_layout.php');
}

function setup() {
  // Preluăm dimensiunile grilei dacă sunt specificate sau le deducem
  if (layoutData.rows && layoutData.cols) {
    rows = layoutData.rows;
    cols = layoutData.cols;
  } else {
    // Deducem dimensiunea grilei din coordonatele maxime
    let maxX = 0, maxY = 0;
    for (let key of ['dock', 'gate', 'shelf', 'obstacle']) {
      if (layoutData[key]) {
        layoutData[key].forEach(p => {
          let {x, y} = getCoord(p);
          if (x > maxX) maxX = x;
          if (y > maxY) maxY = y;
        });
      }
    }
    cols = maxX + 1;
    rows = maxY + 1;
  }

  // Creăm canvas-ul p5 și setăm framerate-ul (opțional, încetinim simularea pentru vizibilitate)
  createCanvas(cols * cellSize, rows * cellSize).parent('sim-container');
  frameRate(10);  // 10 cadre pe secundă pentru a observa mai ușor mișcarea roboților

  // Inițializăm matricea grilei cu obiecte Cell
  for (let y = 0; y < rows; y++) {
    grid[y] = [];
    for (let x = 0; x < cols; x++) {
      grid[y][x] = new Cell(x, y);
    }
  }

  // Marcare celule în funcție de layout (dock, gate, shelf, obstacle)
  if (layoutData.dock) {
    layoutData.dock.forEach(p => {
      let {x, y} = getCoord(p);
      if (y < rows && x < cols) {
        grid[y][x].type = 'dock';
        grid[y][x].walkable = true;  // dock este accesibil (loc de pornire robot)
      }
    });
  }
  if (layoutData.gate) {
    layoutData.gate.forEach(p => {
      let {x, y} = getCoord(p);
      if (y < rows && x < cols) {
        grid[y][x].type = 'gate';
        grid[y][x].walkable = true;  // gate este accesibil (destinație)
      }
    });
    // Dacă există cel puțin o poartă, setăm prima ca destinație implicită pentru livrare
    if (layoutData.gate.length > 0) {
      let {x, y} = getCoord(layoutData.gate[0]);
      gateTarget = {x: x, y: y};
    }
  }
  if (layoutData.shelf) {
    layoutData.shelf.forEach(p => {
      let {x, y} = getCoord(p);
      if (y < rows && x < cols) {
        grid[y][x].type = 'shelf';
        grid[y][x].walkable = false; // raftul blochează mișcarea (obstacol)
      }
    });
  }
  if (layoutData.obstacle) {
    layoutData.obstacle.forEach(p => {
      let {x, y} = getCoord(p);
      if (y < rows && x < cols) {
        grid[y][x].type = 'obstacle';
        grid[y][x].walkable = false;
      }
    });
  }

  // Creăm roboții în pozițiile de dock
  if (layoutData.dock) {
    layoutData.dock.forEach(p => {
      let {x, y} = getCoord(p);
      // Fiecare celulă de dock inițiază un robot
      robots.push(new Robot(x, y));
    });
  }

  // Preluăm lista de sarcini (toate rafturile vor fi vizitate pentru preluarea obiectelor)
  tasks = [];
  if (layoutData.shelf) {
    layoutData.shelf.forEach(p => {
      let coord = getCoord(p);
      tasks.push(coord);
    });
  }

  // Inițializare stări: asignăm fiecărui robot o sarcină inițială (dacă există)
  for (let robot of robots) {
    if (tasks.length > 0) {
      // Luăm următoarea destinație de tip "shelf" din listă
      let targetShelf = tasks.shift();
      robot.state = 'toShelf';
      // Calculăm drumul de la poziția curentă a robotului (dock) la raftul țintă
      let startCell = grid[robot.y][robot.x];
      let goalCell = grid[targetShelf.y][targetShelf.x];
      robot.path = findPath(startCell, goalCell);
      // Notă: dacă un drum nu este găsit (lista rămâne goală), robotul va rămâne pe loc
    } else {
      // Dacă nu mai sunt sarcini, robotul rămâne inactiv
      robot.state = 'idle';
    }
  }
}

function draw() {
  background(255);  // fundal alb pentru canvas
  // Desenăm grila depozitului
  stroke(200);
  for (let y = 0; y < rows; y++) {
    for (let x = 0; x < cols; x++) {
      // Alegem culoarea în funcție de tipul celulei
      let cell = grid[y][x];
      switch(cell.type) {
        case 'obstacle':
          fill(50); break;               // obstacol - gri închis/negru
        case 'shelf':
          fill(150, 100, 50); break;     // raft - maro
        case 'dock':
          fill(100, 150, 255); break;    // dock - albastru deschis
        case 'gate':
          fill(100, 255, 100); break;    // gate - verde deschis
        default:
          fill(255);                    // celulă liberă - alb
      }
      rect(x * cellSize, y * cellSize, cellSize, cellSize);
    }
  }

  // Actualizăm pozițiile roboților (mutăm câte un pas pe frame)
  for (let robot of robots) {
    if (robot.path.length > 0) {
      // Următorul pas din traseu
      let nextStep = robot.path.shift();
      robot.x = nextStep.x;
      robot.y = nextStep.y;
      // Dacă robotul și-a epuizat traseul, va ajunge la destinație în acest frame
      // (gestionăm mai jos schimbarea stării în Hivemind)
    }
  }

  // Logica Hivemind: coordonează sarcinile roboților după mișcare
  for (let robot of robots) {
    // Dacă robotul a ajuns la un raft (traseu 'toShelf' finalizat)
    if (robot.state === 'toShelf' && robot.path.length === 0) {
      // Schimbăm destinația către gate (poartă) după ce a "colectat" obiectul
      if (gateTarget) {
        robot.state = 'toGate';
        // Calculăm drumul de la raftul curent (poziția robotului) la poartă
        let start = grid[robot.y][robot.x];
        let goal = grid[gateTarget.y][gateTarget.x];
        robot.path = findPath(start, goal);
      } else {
        // Dacă nu există poartă definită, marcăm robotul ca idle (nu are unde duce obiectul)
        robot.state = 'idle';
      }
    }
    // Dacă robotul a ajuns la poartă (traseu 'toGate' finalizat)
    if (robot.state === 'toGate' && robot.path.length === 0) {
      // Livrarea este completă, robotul devine liber
      robot.state = 'idle';
    }
    // Dacă robotul este liber și mai există sarcini nealocate, îi atribuim următoarea sarcină (următorul raft)
    if (robot.state === 'idle' && tasks.length > 0) {
      let nextShelf = tasks.shift();
      robot.state = 'toShelf';
      // Calculează traseul de la poziția curentă (unde s-a aflat robotul idle) la următorul raft
      let start = grid[robot.y][robot.x];
      let goal = grid[nextShelf.y][nextShelf.x];
      robot.path = findPath(start, goal);
    }
  }

  // Desenăm roboții pe hartă (deasupra celorlalte elemente)
  // Fiecare robot va fi reprezentat ca un cerc colorat în interiorul celulei sale curente
  noStroke();
  let robotColors = [
    [255, 0, 0],    // roșu
    [255, 165, 0],  // portocaliu
    [0, 200, 255],  // cyan
    [255, 0, 255],  // magenta
    [255, 255, 0],  // galben
    [128, 0, 128]   // violet
  ];
  for (let i = 0; i < robots.length; i++) {
    let robot = robots[i];
    let col = robotColors[i % robotColors.length];
    fill(col[0], col[1], col[2]);
    // Desenăm cercul în centrul celulei robotului
    ellipse(robot.x * cellSize + cellSize/2, robot.y * cellSize + cellSize/2, cellSize * 0.6, cellSize * 0.6);
  }
}
