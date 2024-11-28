window.idleTimeoutInitialized||(window.idleTimeoutInitialized=!0,document.addEventListener("alpine:init",()=>{let r=new BroadcastChannel("idleTimeoutChannel");if(!Alpine.store("idleTimeoutStore")){Alpine.store("idleTimeoutStore",{idleDurationSecs:null,warningBefore:null,timeLeftSecs:null,warningDisplayed:!1,timer:null,resetCountdown(e=!1){this.timeLeftSecs=this.idleDurationSecs,this.warningDisplayed=!1,this.timer&&clearInterval(this.timer),e||r.postMessage({type:"resetCountdown"}),this.startCountdown()},startCountdown(){this.timer&&clearInterval(this.timer),this.timer=setInterval(()=>{this.timeLeftSecs>0?(this.timeLeftSecs--,!this.warningDisplayed&&this.timeLeftSecs<=this.warningBefore&&this.showWarning()):(clearInterval(this.timer),this.logoutUser())},1e3)},showWarning(){if(!this.warningDisplayed){let e=this.warningBefore,o=Math.floor(e/60),i=e%60,s="";o>0&&(s+=`${o} minute${o!==1?"s":""}`),(i>0||o===0)&&(o>0&&(s+=" "),s+=`${i} second${i!==1?"s":""}`),new FilamentNotification().title("Your session is about to expire").body(`You will be logged out in ${s}.`).danger().color("danger").send(),this.warningDisplayed=!0}},logoutUser(){this.timer&&clearInterval(this.timer),document.getElementById("auto-logout-form").submit()}});let t=Alpine.store("idleTimeoutStore"),n=document.getElementById("auto-logout-form");if(n){if(!(n.dataset.autoLogoutEnabled==="1"))return;t.idleDurationSecs=parseInt(n.dataset.duration,10),t.warningBefore=parseInt(n.dataset.warnBefore,10),t.timeLeftSecs=t.idleDurationSecs}else return;t.resetCountdown();let a=["mousemove","keypress","click","touchstart","focus","change","mouseover","mouseout","mousedown","mouseup","keydown","keyup","submit","reset","select","scroll"];if(window.idleTimeoutEventListenersAttached||(a.forEach(e=>{window.addEventListener(e,()=>{r.postMessage({type:"resetCountdown"}),t.resetCountdown(!0)})}),window.idleTimeoutEventListenersAttached=!0),r.addEventListener("message",function(e){e.data?.type==="resetCountdown"?t.resetCountdown(!0):e.data?.type==="showWarning"?t.showWarning():e.data?.type==="logout"&&t.logoutUser()}),n.dataset.showTimeleft==="1"){let e=document.getElementById("idle-timeout-element");e||(e=document.createElement("div"),e.id="idle-timeout-element",document.body.appendChild(e));let o=n.dataset.timeLeftText||null;e.setAttribute("x-data",JSON.stringify({timeLeftText:o}));let i=e.querySelector("#timer-display");i||(i=document.createElement("div"),i.id="timer-display",e.appendChild(i)),i.setAttribute("x-text",'timeLeftText + Math.floor($store.idleTimeoutStore.timeLeftSecs / 60) + "m " + ($store.idleTimeoutStore.timeLeftSecs % 60) + "s"'),Alpine.initTree(e)}}}));
