
const API_BASE='http://localhost/event-api';
const $=s=>document.querySelector(s), $$=s=>[...document.querySelectorAll(s)];
async function api(path,opt={}){const r=await fetch(API_BASE+path,{headers:{'Content-Type':'application/json',...(opt.headers||{})},...opt});let j={};try{j=await r.json()}catch(e){}if(!r.ok||j.status==='error')throw new Error(j.message||`Request failed (${r.status})`);return j}
function data(j){return Array.isArray(j)?j:(j.data||[])}
function esc(v=''){return String(v??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]))}
function money(v){return 'RM '+Number(v||0).toFixed(2)}
function dt(v){if(!v)return '-';const d=new Date(String(v).replace(' ','T'));return isNaN(d)?v:d.toLocaleString('en-MY',{dateStyle:'medium',timeStyle:'short'})}
function auth(role){const r=localStorage.getItem('role');if(!r){location='login.html';return false}if(role&&r!==role){route(r);return false}const n=localStorage.getItem('full_name')||r;$$('[data-user]').forEach(x=>x.textContent=n);$$('[data-role]').forEach(x=>x.textContent=r);$$('[data-avatar]').forEach(x=>x.textContent=n.charAt(0).toUpperCase());return true}
function route(r){location=r==='Administrator'?'admin-dashboard.html':r==='Organiser'?'organiser-dashboard.html':'customer-dashboard.html'}
function logout(){localStorage.clear();location='login.html'}
function nav(id){$$('.view').forEach(v=>v.classList.add('hide'));$('#'+id)?.classList.remove('hide');$$('.nav button').forEach(b=>b.classList.toggle('active',b.dataset.view===id));$('.side')?.classList.remove('open')}
function modal(id,on=true){$('#'+id)?.classList.toggle('show',on)}
function msg(text,ok=true){const n=$('#notice');if(!n)return;n.textContent=text;n.className='notice show '+(ok?'ok':'err');setTimeout(()=>n.classList.remove('show'),3500)}
