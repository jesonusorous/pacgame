<?php
session_start(); // Start the session

// Restrict access to admin users only
if (!isset($_SESSION['username']) || $_SESSION['role'] == 'admin') {
    header("Location: index.php");
    exit();
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8" />
<title>Pac-Man Game</title>
<style>
:root{
  --bg:#000;
  --wall:#003b88; 
  --pellet:#ffd;
  --power:#ff0;
  --pac:#ffcc00;
  --ghost1:#ff3860;
  --ghost2:#66ccff;
  --ghost3:#ff8c00;
  --ghost4:#9b59b6;
}

html,body{
  height:100%;
  margin:0;
  background:#031226;
  display:flex;
  align-items:center;
  justify-content:center;
  font-family:Arial,Helvetica,sans-serif;
  color:#fff;
}

#gameCard{
  background:#062544;
  padding:18px;
  border-radius:12px;
  box-shadow:0 6px 18px rgba(0,0,0,.6);
  text-align:center;
  position: relative;
}

canvas{
  display:block;
  background:var(--bg);
  border:8px solid var(--wall);
  margin:10px auto;
}

.info{
  display:flex;
  gap:12px;
  justify-content:center;
  align-items:center;
  flex-wrap: wrap;
}

.info div{
  padding:6px 10px;
  background:rgba(0,0,0,0.15);
  border-radius:6px;
}

button{
  background:var(--wall);
  color:#fff;
  border:none;
  padding:8px 12px;
  border-radius:6px;
  cursor:pointer;
}

button:hover{
  filter:brightness(1.1);
}

small{
  display:block;
  color:#ccc;
  margin-top:6px;
}

/* MODALS */
#exitModal, #levelModal, #gameOverModal {
  position: absolute;
  top: 0; left: 0;
  width: 100%; height: 100%;
  display: none;
  align-items: center;
  justify-content: center;
  z-index: 100;
   background-color: rgba(0, 0, 0, 0.5); /* black with 50% transparency */
}

#exitModal div, #levelModal div, #gameOverModal div{
  background: #062544;
  padding: 20px 30px;
  border-radius: 12px;
  text-align: center;
  box-shadow: 0 6px 18px rgba(0,0,0,0.6);
  color: #fff;
}

#exitModal button, #levelModal button, #gameOverModal button {
  margin: 10px 8px 0 8px;
  padding: 8px 14px;
  background: var(--wall);
  border: none;
  color: #fff;
  border-radius: 6px;
  cursor: pointer;
}

#exitModal button:hover, #levelModal button:hover, #gameOverModal button:hover {
  filter: brightness(1.1);
}

#gameOverModal h1{
  font-size: 48px;
  color: #ff3860;
  margin-bottom: 20px;
}

#gameOverModal p{
  font-size: 28px;
  margin-bottom: 25px;
}
</style>
</head>

<body>
<div id="gameCard">
<h2>Pac-Man Game</h2>

<!-- EXIT MODAL -->
<div id="exitModal">
  <div>
    <p>Are you sure you want to exit?</p>
    <button id="confirmExit">Yes</button>
    <button id="cancelExit">No</button>
  </div>
</div>

<!-- LEVEL CLEARED MODAL -->
<div id="levelModal">
  <div>
    <p id="levelText"></p>
    <button id="levelOk">OK</button>
  </div>
</div>

<!-- GAME OVER MODAL -->
<div id="gameOverModal">
  <div>
    <h1>GAME OVER</h1>
    <p>Your Score: <span id="finalScore">0</span></p>
    <button id="restartGame">Restart</button>
    <button id="goDashboard">Dashboard</button>
  </div>
</div>

<canvas id="c" width="660" height="660"></canvas>

<div class="info">
  <div>Score: <span id="score">0</span></div>
  <div>Lives: <span id="lives">3</span></div>
  <div>Level: <span id="level">1</span></div>
  <button id="restart">Restart</button>
  <button id="exit">Exit</button>
</div>
<small>Use Arrow keys or WASD to move</small>
</div>

<script>
const canvas = document.getElementById('c');
const ctx = canvas.getContext('2d');
const S = canvas.width;
const ROWS = 22;
const COLS = 22;
const cell = Math.floor(S / ROWS);
const pelletRadius = cell*0.08;
const powerRadius = cell*0.18;

let scoreEl = document.getElementById('score');
let livesEl = document.getElementById('lives');
let levelEl = document.getElementById('level');
let restartBtn = document.getElementById('restart');

let baseGhosts = 4;

// MODAL ELEMENTS
let levelModal = document.getElementById("levelModal");
let levelText = document.getElementById("levelText");
let levelOk = document.getElementById("levelOk");
let gameOverModal = document.getElementById("gameOverModal");
let finalScore = document.getElementById("finalScore");

// EXIT MODAL
let exitModal = document.getElementById('exitModal');
let confirmExit = document.getElementById('confirmExit');
let cancelExit = document.getElementById('cancelExit');
let exitBtn = document.getElementById('exit');

// MAP TEMPLATE
let mapTemplate = [
[1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1],
[1,0,2,0,2,2,2,2,2,1,2,2,2,2,2,2,2,2,2,2,2,1],
[1,2,1,2,1,2,1,1,2,1,2,1,1,1,1,2,1,1,1,1,2,1],
[1,3,1,2,1,2,1,0,2,1,2,2,0,1,0,2,1,0,1,1,2,1],
[1,2,1,2,1,2,1,0,2,1,1,1,0,1,0,2,1,0,1,3,2,1],
[1,2,2,0,2,2,2,0,2,2,2,2,0,2,0,2,2,0,2,0,2,1],
[1,2,1,0,1,1,2,0,1,1,2,1,0,1,1,1,2,0,1,0,2,1],
[1,2,1,0,1,1,2,0,1,1,2,1,0,1,1,1,2,0,1,0,2,1],
[1,2,2,0,2,2,2,0,2,2,2,2,0,2,2,2,2,0,2,0,2,1],
[1,1,1,0,1,1,2,0,1,1,1,1,0,1,1,1,2,0,1,0,2,1],
[1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1],
[1,2,1,1,1,2,1,0,1,1,2,1,0,1,0,1,1,0,1,0,2,1],
[1,2,2,2,1,2,2,0,2,2,2,1,0,2,0,2,2,0,2,0,2,1],
[1,1,1,2,1,1,1,0,1,1,2,1,0,1,0,1,1,0,1,0,1,1],
[1,2,2,3,2,2,2,0,2,2,2,2,0,2,0,2,2,0,2,0,2,1],
[1,2,1,1,1,1,2,0,1,1,1,1,0,1,0,1,1,0,1,0,2,1],
[1,2,2,2,2,2,2,0,2,2,2,2,0,2,0,2,2,0,2,0,2,1],
[1,2,1,1,1,2,1,0,1,1,1,1,0,1,0,1,1,0,1,0,2,1],
[1,2,2,2,1,2,2,0,2,2,2,2,0,2,0,2,2,0,3,0,2,1],
[1,2,1,0,1,1,1,0,1,1,1,1,0,1,0,1,1,0,1,0,2,1],
[1,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,1],
[1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1]
];

let map, pelletsTotal;
let score=0, lives=3, level=1;
let gameOver=false;
let powerTimer=0;
let animFrameId = null;

let pac = {x: cell*1.5, y: cell*1.5, radius: cell*0.42, speed:2, dir:null, nextDir:null, vx:0, vy:0};
let ghosts=[];

/* -------------------------------------------
       RESET MAP + DYNAMIC GHOST COUNT
------------------------------------------- */
function resetMap(){
  map = mapTemplate.map(r=>r.slice());
  pelletsTotal = 0;
  for(let y=0;y<ROWS;y++) 
    for(let x=0;x<COLS;x++) 
      if(map[y][x]===2||map[y][x]===3) pelletsTotal++;

  pac.x = cell*1.5; 
  pac.y = cell*1.5; 
  pac.dir=null; pac.nextDir=null; pac.vx=0; pac.vy=0;

  ghosts = [];
  const ghostColors = ['--ghost1','--ghost2','--ghost3','--ghost4'];

  for(let i=0;i<baseGhosts;i++){
    ghosts.push({
      x:10.5*cell,
      y:10.5*cell,
      color:getComputedStyle(document.documentElement).getPropertyValue(
        ghostColors[i % ghostColors.length]
      ).trim(),
      dir:Math.floor(Math.random()*4),
      edible:false
    });
  }

  scoreEl.textContent=score; 
  livesEl.textContent=lives; 
  levelEl.textContent=level;
  gameOver=false; 
  powerTimer=0;
}

/* -------------------------------------------
          DRAW FUNCTIONS
------------------------------------------- */
function drawMap(){
  ctx.clearRect(0,0,S,S);
  for(let y=0;y<ROWS;y++){
    for(let x=0;x<COLS;x++){
      const val = map[y][x];
      let px = x*cell, py=y*cell;
      if(val===1){ 
        ctx.fillStyle=getComputedStyle(document.documentElement).getPropertyValue('--wall').trim();
        let pad=Math.floor(cell*0.08); ctx.fillRect(px+pad,py+pad,cell-2*pad,cell-2*pad);
      }
      else if(val===2){ 
        ctx.fillStyle=getComputedStyle(document.documentElement).getPropertyValue('--pellet').trim();
        ctx.beginPath(); ctx.arc(px+cell/2,py+cell/2,pelletRadius,0,Math.PI*2); ctx.fill();
      }
      else if(val===3){ 
        ctx.fillStyle=getComputedStyle(document.documentElement).getPropertyValue('--power').trim();
        ctx.beginPath(); ctx.arc(px+cell/2,py+cell/2,powerRadius,0,Math.PI*2); ctx.fill();
      }
    }
  }
}

function drawPac(){
  ctx.fillStyle=getComputedStyle(document.documentElement).getPropertyValue('--pac').trim();
  ctx.beginPath(); ctx.arc(pac.x,pac.y,pac.radius,0,Math.PI*2); ctx.fill();
  ctx.fillStyle="#000"; ctx.beginPath(); ctx.arc(pac.x+pac.radius*0.2,pac.y-pac.radius*0.3,pac.radius*0.2,0,Math.PI*2); ctx.fill();
}

function drawGhost(g){
  let px=g.x, py=g.y, w=cell*0.9, h=cell*0.9;
  ctx.fillStyle=g.edible?'#88bfff':g.color;
  ctx.beginPath(); ctx.ellipse(px, py-h*0.05, w*0.44, h*0.42, 0, Math.PI, 0, true);
  ctx.fillRect(px-w*0.44,py-h*0.05,w*0.88,h*0.5);
  let steps=4;
  for(let i=0;i<steps;i++){ 
    let cx=px-w*0.44+(i+0.5)*(w/steps); 
    ctx.beginPath(); 
    ctx.arc(cx,py+h*0.25,w/steps*0.42,0,Math.PI,true); 
    ctx.fill();
  }
  ctx.fillStyle="#fff";
  ctx.beginPath(); ctx.arc(px-w*0.16,py-h*0.02,w*0.11,0,Math.PI*2); ctx.fill();
  ctx.beginPath(); ctx.arc(px+w*0.16,py-h*0.02,w*0.11,0,Math.PI*2); ctx.fill();
  ctx.fillStyle="#000";
  ctx.beginPath(); ctx.arc(px-w*0.16,py-h*0.02,w*0.05,0,Math.PI*2); ctx.fill();
  ctx.beginPath(); ctx.arc(px+w*0.16,py-h*0.02,w*0.05,0,Math.PI*2); ctx.fill();
}

/* -------------------------------------------
               PAC & GHOST LOGIC
------------------------------------------- */
function isWalkableTile(x,y){ return map[y] && map[y][x]!==1; }

function pelletCheckPixel(){
  let tileX=Math.floor(pac.x/cell), tileY=Math.floor(pac.y/cell);
  let v=map[tileY][tileX];
  if(v===2||v===3){
    map[tileY][tileX]=0;
    score += v===2?10:50; scoreEl.textContent=score; pelletsTotal--;
    if(v===3){ powerTimer=400; for(let g of ghosts) g.edible=true;}
  }
}

function movePacPixel(){
  const dirs=[[0,-1],[1,0],[0,1],[-1,0]];
  let tileX=Math.floor(pac.x/cell), tileY=Math.floor(pac.y/cell);
  let centerX=tileX*cell+cell/2, centerY=tileY*cell+cell/2;

  if(Math.abs(pac.x-centerX)<pac.speed) pac.x=centerX;
  if(Math.abs(pac.y-centerY)<pac.speed) pac.y=centerY;

  if(pac.x===centerX && pac.y===centerY && pac.nextDir!=null){
    let d=dirs[pac.nextDir];
    if(isWalkableTile(tileX+d[0],tileY+d[1])){ 
      pac.dir=pac.nextDir; pac.nextDir=null; 
      pac.vx=d[0]*pac.speed; pac.vy=d[1]*pac.speed; 
    }
  }

  if(pac.dir!=null){
    let nextTileX = Math.floor((pac.x + pac.vx)/cell);
    let nextTileY = Math.floor((pac.y + pac.vy)/cell);
    if(isWalkableTile(nextTileX,nextTileY)){
      pac.x += pac.vx; pac.y += pac.vy;
    } else {
      pac.vx=0; pac.vy=0;
      pac.x=centerX; pac.y=centerY;
      pac.dir=null;
    }
  }

  pelletCheckPixel();
}

function moveGhostsSmart(){
  const dirs=[[0,-1],[1,0],[0,1],[-1,0]];
  for(let g of ghosts){
    let tileX=Math.floor(g.x/cell), tileY=Math.floor(g.y/cell);
    let centerX=tileX*cell+cell/2, centerY=tileY*cell+cell/2;

    if(Math.abs(g.x-centerX)<1 && Math.abs(g.y-centerY)<1){
      g.x=centerX; g.y=centerY;
      let d = dirs[g.dir];
      if(!isWalkableTile(tileX+d[0],tileY+d[1])){
        let options=[];
        dirs.forEach((dir,i)=>{ 
          if(isWalkableTile(tileX+dir[0],tileY+dir[1]) && i !== (g.dir+2)%4) 
            options.push(i); 
        });
        if(options.length>0) g.dir=options[Math.floor(Math.random()*options.length)];
      } else {
        let options=[];
        dirs.forEach((dir,i)=>{ 
          if(isWalkableTile(tileX+dir[0],tileY+dir[1]) && i !== (g.dir+2)%4) 
            options.push(i); 
        });
        if(options.length>1 && Math.random()<0.02) 
          g.dir=options[Math.floor(Math.random()*options.length)];
      }
    }

    let d = dirs[g.dir];
    g.x += d[0]*1.5; g.y += d[1]*1.5;
  }
}

function ghostCollisionCheck(){
  for(let g of ghosts){
    if(Math.hypot(pac.x-g.x,pac.y-g.y)<pac.radius){
      if(g.edible){ 
        g.x=10.5*cell; g.y=10.5*cell; 
        g.edible=false; 
        score+=200; scoreEl.textContent=score;
      }
      else loseLife();
    }
  }
}

function loseLife(){
  lives--; 
  livesEl.textContent = lives;

  if(lives <= 0){ 
    gameOver = true; 

    // Send score to PHP
    fetch('save_score.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'score=' + score
    }).then(() => {
        // Show Game Over modal
        finalScore.textContent = score;
        gameOverModal.style.display = 'flex';
    });
  } else {
    pac.x = cell*1.5; pac.y = cell*1.5; pac.dir = null; pac.nextDir = null; pac.vx = 0; pac.vy = 0;
    for(let g of ghosts){ g.x = 10.5*cell; g.y = 10.5*cell; g.edible = false; }
    powerTimer = 0;
  }
}

/* -------------------------------------------
             KEYBOARD INPUT
------------------------------------------- */
document.addEventListener('keydown',e=>{
  if(e.key.startsWith('Arrow')) e.preventDefault();
  if(e.key==='ArrowUp'||e.key==='w'||e.key==='W') pac.nextDir=0;
  if(e.key==='ArrowRight'||e.key==='d'||e.key==='D') pac.nextDir=1;
  if(e.key==='ArrowDown'||e.key==='s'||e.key==='S') pac.nextDir=2;
  if(e.key==='ArrowLeft'||e.key==='a'||e.key==='A') pac.nextDir=3;
});

/* -------------------------------------------
                LEVEL CLEAR
------------------------------------------- */
levelOk.onclick = () => {
  levelModal.style.display = "none";
  resetMap();
  startGameLoop();
};

/* -------------------------------------------
                GAME LOOP
------------------------------------------- */
function startGameLoop(){
  if(animFrameId) cancelAnimationFrame(animFrameId);

  function loop(){
    if(!gameOver){
      drawMap(); 
      movePacPixel(); 
      moveGhostsSmart();

      for(let g of ghosts) drawGhost(g);
      drawPac();

      if(powerTimer>0){ 
        powerTimer--; 
        if(powerTimer===0) for(let g of ghosts) g.edible=false;
      }

      ghostCollisionCheck();

      if(pelletsTotal<=0){
        level++; 
        baseGhosts += 3;
        levelEl.textContent=level;

        // Show LEVEL CLEARED popup
        levelText.textContent = "LEVEL CLEARED! Next Level: " + level;
        levelModal.style.display = 'flex';

        cancelAnimationFrame(animFrameId);
        return;
      }

      animFrameId = requestAnimationFrame(loop);
    }
  }
  loop();
}

/* -------------------------------------------
               EXIT BUTTON
------------------------------------------- */
exitBtn.onclick = () => exitModal.style.display = 'flex';
cancelExit.onclick = () => exitModal.style.display = 'none';
confirmExit.onclick = () => window.location.href = 'dashboard.php';

/* -------------------------------------------
          GAME OVER BUTTONS
------------------------------------------- */
document.getElementById('restartGame').onclick = () => {
  gameOverModal.style.display = 'none';
  score = 0;
  lives = 3;
  level = 1;
  baseGhosts = 4;
  resetMap();
  startGameLoop();
};

document.getElementById('goDashboard').onclick = () => {
  window.location.href = 'dashboard.php';
};

/* -------------------------------------------
                RESTART BUTTON
------------------------------------------- */
restartBtn.onclick = () => {
  score = 0;
  lives = 3;
  level = 1;
  baseGhosts = 4;
  resetMap();
  startGameLoop();
};

/* -------------------------------------------
            INITIALIZE GAME
------------------------------------------- */
resetMap();
startGameLoop();
</script>
</body>
</html>
