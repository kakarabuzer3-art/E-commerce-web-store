const money = amount => `PKR ${new Intl.NumberFormat("en-PK",{minimumFractionDigits:2,maximumFractionDigits:2}).format(amount)}`;
const grid = document.querySelector("#product-grid");
const searchInput = document.querySelector("#search-input");
const sortSelect = document.querySelector("#sort-select");
const countLabel = document.querySelector("#results-count");
const emptyState = document.querySelector("#empty-state");
const clearFilters = document.querySelector("#clear-filters");
let products = [];
let cart = {items:[],subtotal:0,shipping:{standard:5.95,express:12.95},freeShippingThreshold:75};
let csrfToken = "";
let activeCategory = "All";
let favorites = new Set(JSON.parse(localStorage.getItem("sunday-favorites") || "[]"));
let showFavorites = false;
let toastTimer;

function escapeHtml(value){
  return String(value).replace(/[&<>"']/g,char=>({"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#39;"}[char]));
}

function deliveryStatusLabel(status){
  return ({out_for_delivery:"Out for delivery"}[status]||status.replaceAll("_"," ").replace(/\b\w/g,char=>char.toUpperCase()));
}

async function request(url, options={}){
  const headers={"Accept":"application/json",...(options.body?{"Content-Type":"application/json"}:{}),...(options.headers||{})};
  if(options.method&&options.method!=="GET")headers["X-CSRF-Token"]=csrfToken;
  const response=await fetch(url,{credentials:"same-origin",...options,headers});
  const data=await response.json().catch(()=>({error:"The store returned an invalid response."}));
  if(!response.ok)throw new Error(data.error||"The request could not be completed.");
  return data;
}

function getVisibleProducts(){
  const query=searchInput.value.trim().toLowerCase();
  let visible=products.filter(product=>{
    const categoryMatch=activeCategory==="All"||product.category===activeCategory;
    const favoriteMatch=!showFavorites||favorites.has(product.id);
    const searchMatch=!query||`${product.name} ${product.category} ${product.type} ${product.desc}`.toLowerCase().includes(query);
    return categoryMatch&&favoriteMatch&&searchMatch;
  });
  if(sortSelect.value==="low")visible.sort((a,b)=>a.price-b.price);
  if(sortSelect.value==="high")visible.sort((a,b)=>b.price-a.price);
  if(sortSelect.value==="rating")visible.sort((a,b)=>Number(b.rating)-Number(a.rating));
  return visible;
}

function renderProducts(){
  const visible=getVisibleProducts();
  countLabel.textContent=showFavorites?`${visible.length} favorite${visible.length===1?"":"s"}`:`Showing ${visible.length} ${visible.length===1?"piece":"pieces"}`;
  grid.innerHTML=visible.map(product=>`
    <article class="product-card">
      <div class="product-image-wrap" style="background:${escapeHtml(product.color||"#e8e7df")}">
        <img src="${escapeHtml(product.image)}" alt="${escapeHtml(product.name)}" loading="lazy">
        ${product.badge?`<span class="product-badge ${product.badge==="JUST LANDED"?"sale":""}">${escapeHtml(product.badge)}</span>`:""}
        <button class="favorite-button ${favorites.has(product.id)?"active":""}" type="button" data-favorite="${product.id}" aria-label="${favorites.has(product.id)?"Remove":"Add"} ${escapeHtml(product.name)} ${favorites.has(product.id)?"from":"to"} favorites" aria-pressed="${favorites.has(product.id)}">${favorites.has(product.id)?"♥":"♡"}</button>
      </div>
      <div class="product-info">
        <div class="product-meta"><span>${escapeHtml(product.category)} · ${escapeHtml(product.type)}</span><span class="rating">★ ${Number(product.rating).toFixed(1)}</span></div>
        <div class="product-title-row"><h3 class="product-title">${escapeHtml(product.name)}</h3><span class="product-price">${money(product.price)}</span></div>
        <p class="product-description">${escapeHtml(product.desc)}</p>
        ${product.stock<5&&product.stock>0?`<p class="stock-note">Only ${product.stock} left</p>`:""}
        <button class="add-button" type="button" data-add="${product.id}" ${product.stock<1?"disabled":""}>${product.stock<1?"Sold out":"Add to bag"} <span>+</span></button>
      </div>
    </article>`).join("");
  emptyState.hidden=visible.length>0;
  grid.hidden=visible.length===0;
  clearFilters.hidden=!(activeCategory!=="All"||searchInput.value||showFavorites);
  document.querySelectorAll(".category-chip").forEach(button=>button.classList.toggle("active",button.dataset.category===activeCategory));
}

function renderCart(){
  const items=cart.items||[];
  const count=items.reduce((sum,item)=>sum+item.quantity,0);
  const standard=cart.subtotal>=cart.freeShippingThreshold?0:cart.shipping.standard;
  const total=cart.subtotal+standard;
  document.querySelectorAll(".cart-count").forEach(el=>el.textContent=count);
  document.querySelector("#drawer-item-count").textContent=`(${count})`;
  document.querySelector("#cart-subtotal").textContent=money(cart.subtotal);
  document.querySelector("#cart-shipping").textContent=standard===0?"FREE":money(standard);
  document.querySelector("#cart-total").textContent=money(total);
  const remaining=Math.max(0,cart.freeShippingThreshold-cart.subtotal);
  document.querySelector("#shipping-message").textContent=count===0?`Free standard delivery on orders over ${money(cart.freeShippingThreshold)}`:remaining===0?"You've unlocked free standard shipping!":`Add ${money(remaining)} more to unlock free standard shipping`;
  document.querySelector("#shipping-progress-fill").style.width=count===0?"0%":`${Math.min(100,cart.subtotal/cart.freeShippingThreshold*100)}%`;
  document.querySelector("#cart-items").innerHTML=items.map(item=>`
    <article class="cart-item">
      <img src="${escapeHtml(item.image)}" alt="${escapeHtml(item.name)}">
      <div><h3>${escapeHtml(item.name)}</h3><p>${escapeHtml(item.type)}</p>
        <div class="quantity-control"><button type="button" data-quantity="${item.id}" data-change="-1" aria-label="Decrease quantity">−</button><span>${item.quantity}</span><button type="button" data-quantity="${item.id}" data-change="1" aria-label="Increase quantity" ${item.quantity>=item.stock?"disabled":""}>+</button></div>
      </div>
      <div><div class="cart-item-price">${money(item.price*item.quantity)}</div><button class="remove-item" type="button" data-remove="${item.id}">Remove</button></div>
    </article>`).join("");
  document.querySelector("#cart-empty").classList.toggle("visible",count===0);
  document.querySelector("#cart-footer").hidden=count===0;
  document.querySelector("#cart-items").hidden=count===0;
  document.querySelectorAll(".checkout-button").forEach(button=>{if(button.id==="checkout-button")button.disabled=count===0;});
}

function toast(message){
  const element=document.querySelector("#toast");
  element.textContent=message;element.classList.add("show");
  clearTimeout(toastTimer);toastTimer=setTimeout(()=>element.classList.remove("show"),2600);
}

async function refreshCart(){
  cart=await request("api.php?route=cart");
  renderCart();
}

function openCart(){
  document.querySelector("#cart-drawer").classList.add("open");
  document.querySelector("#cart-drawer").setAttribute("aria-hidden","false");
  document.querySelector("#drawer-backdrop").classList.add("open");
  document.body.classList.add("drawer-open");
  document.querySelector(".close-drawer").focus();
}

function closeCart(){
  const wasOpen=document.querySelector("#cart-drawer").classList.contains("open");
  document.querySelector("#cart-drawer").classList.remove("open");
  document.querySelector("#cart-drawer").setAttribute("aria-hidden","true");
  document.querySelector("#drawer-backdrop").classList.remove("open");
  document.body.classList.remove("drawer-open");
  if(wasOpen)document.querySelector(".cart-toggle").focus();
}

function updateCheckoutTotal(){
  const shipping=document.querySelector('#checkout-form [name="shipping"]:checked')?.value||"standard";
  const shippingPrice=shipping==="express"?cart.shipping.express:(cart.subtotal>=cart.freeShippingThreshold?0:cart.shipping.standard);
  document.querySelector("#checkout-subtotal").textContent=money(cart.subtotal);
  document.querySelector("#checkout-shipping").textContent=shippingPrice===0?"FREE":money(shippingPrice);
  document.querySelector("#checkout-total").textContent=money(cart.subtotal+shippingPrice);
  document.querySelector("#standard-shipping-price").textContent=cart.subtotal>=cart.freeShippingThreshold?"FREE":money(cart.shipping.standard);
  document.querySelector("#express-shipping-price").textContent=money(cart.shipping.express);
}

function openCheckout(){
  if(!cart.items.length){toast("Your bag is empty.");return;}
  const form=document.querySelector("#checkout-form");
  form.hidden=false;
  document.querySelector("#order-success").hidden=true;
  document.querySelector("#checkout-error").textContent="";
  form.reset();
  syncCheckoutCountry();
  updateCheckoutTotal();
  document.querySelector("#checkout-dialog").showModal();
}

function syncCheckoutCountry(){
  const input=document.querySelector("#country-search");
  const code=document.querySelector("#country-code");
  const option=[...document.querySelector("#country-options").options].find(entry=>entry.value.toLocaleLowerCase()===input.value.trim().toLocaleLowerCase());
  code.value=option?.dataset.code||"";
  input.setCustomValidity(option?"":"Choose a country from the list.");
}

document.querySelector("#country-search").addEventListener("input",syncCheckoutCountry);
document.querySelector("#shop").addEventListener("click",async event=>{
  const add=event.target.closest("[data-add]");
  const favorite=event.target.closest("[data-favorite]");
  const category=event.target.closest(".category-chip");
  if(add){
    try{
      await request("api.php?route=cart",{method:"POST",body:JSON.stringify({id:Number(add.dataset.add),quantity:1})});
      await refreshCart();
      const product=products.find(item=>item.id===Number(add.dataset.add));
      toast(`${product.name} added to your bag`);
    }catch(error){toast(error.message);}
  }
  if(favorite){
    const id=Number(favorite.dataset.favorite);
    favorites.has(id)?favorites.delete(id):favorites.add(id);
    localStorage.setItem("sunday-favorites",JSON.stringify([...favorites]));
    renderProducts();document.querySelector(".favorite-count").textContent=favorites.size;
  }
  if(category){activeCategory=category.dataset.category;showFavorites=false;renderProducts();}
});
document.querySelector(".favorite-count").textContent=favorites.size;
document.querySelectorAll(".category-chip").forEach(button=>button.addEventListener("click",()=>{activeCategory=button.dataset.category;showFavorites=false;renderProducts();}));
document.querySelectorAll("[data-nav-category]").forEach(link=>link.addEventListener("click",()=>{
  activeCategory=link.dataset.navCategory;showFavorites=false;renderProducts();
  document.querySelector("#main-nav").classList.remove("open");
  document.querySelector(".menu-toggle").setAttribute("aria-expanded","false");
}));
searchInput.addEventListener("input",renderProducts);
sortSelect.addEventListener("change",renderProducts);
document.querySelector(".search-toggle").addEventListener("click",()=>{document.querySelector("#shop").scrollIntoView({behavior:"smooth"});setTimeout(()=>searchInput.focus(),350);});
document.querySelector(".favorites-toggle").addEventListener("click",()=>{showFavorites=!showFavorites;activeCategory="All";renderProducts();document.querySelector("#shop").scrollIntoView({behavior:"smooth"});});
document.querySelector("#clear-filters").addEventListener("click",()=>{activeCategory="All";showFavorites=false;searchInput.value="";renderProducts();});
document.querySelector("#reset-shop").addEventListener("click",()=>{activeCategory="All";showFavorites=false;searchInput.value="";renderProducts();});
document.querySelector(".cart-toggle").addEventListener("click",openCart);
document.querySelector(".close-drawer").addEventListener("click",closeCart);
document.querySelector("#drawer-backdrop").addEventListener("click",closeCart);
document.querySelector(".continue-shopping").addEventListener("click",closeCart);
document.querySelector("#checkout-button").addEventListener("click",()=>{closeCart();openCheckout();});

document.querySelector("#cart-items").addEventListener("click",async event=>{
  const quantity=event.target.closest("[data-quantity]");
  const remove=event.target.closest("[data-remove]");
  const id=Number(quantity?.dataset.quantity||remove?.dataset.remove);
  if(!id)return;
  try{
    if(remove){await request("api.php?route=cart",{method:"DELETE",body:JSON.stringify({id})});}
    else{
      const item=cart.items.find(entry=>entry.id===id);
      const next=Math.max(0,item.quantity+Number(quantity.dataset.change));
      await request("api.php?route=cart",{method:"PUT",body:JSON.stringify({id,quantity:next})});
    }
    await refreshCart();
  }catch(error){toast(error.message);}
});

document.querySelectorAll('#checkout-form [name="shipping"]').forEach(radio=>radio.addEventListener("change",updateCheckoutTotal));
document.querySelector("#checkout-form").addEventListener("submit",async event=>{
  event.preventDefault();
  const form=event.currentTarget;
  const submit=document.querySelector("#place-order-button");
  const errorBox=document.querySelector("#checkout-error");
  if(!form.reportValidity())return;
  submit.disabled=true;errorBox.textContent="";
  const formData=new FormData(form);
  const customer=Object.fromEntries(["name","email","phone","address1","city","country","note"].map(key=>[key,formData.get(key)||""]));
  try{
    const order=await request("api.php?route=checkout",{method:"POST",body:JSON.stringify({customer,shipping:formData.get("shipping")})});
    await refreshCart();
    form.hidden=true;
    document.querySelector("#order-success").hidden=false;
    document.querySelector("#success-copy").textContent=`Thanks${customer.name?`, ${customer.name}`:""}! Your ${money(order.total)} order is confirmed. Save your order number and access code to track delivery.`;
    document.querySelector("#success-order-number").textContent=`Order: ${order.orderNumber}`;
    document.querySelector("#success-access-code").textContent=`Access code: ${order.accessCode}`;
    document.querySelector("#success-delivery").textContent=order.estimatedDelivery;
    document.querySelector("#track-new-order").dataset.order=order.orderNumber;
    document.querySelector("#track-new-order").dataset.code=order.accessCode;
  }catch(error){errorBox.textContent=error.message;}
  finally{submit.disabled=false;}
});

document.querySelector("#track-new-order").addEventListener("click",async event=>{
  document.querySelector("#checkout-dialog").close();
  document.querySelector("#tracking-dialog").showModal();
  const form=document.querySelector("#tracking-form");
  form.elements.orderNumber.value=event.currentTarget.dataset.order||"";
  form.elements.accessCode.value=event.currentTarget.dataset.code||"";
  if(form.elements.orderNumber.value)form.requestSubmit();
});
document.querySelectorAll(".open-tracking").forEach(link=>link.addEventListener("click",event=>{event.preventDefault();document.querySelector("#tracking-dialog").showModal();}));
document.querySelector("#tracking-form").addEventListener("submit",async event=>{
  event.preventDefault();
  const form=event.currentTarget,button=form.querySelector("button[type=submit]");
  const error=document.querySelector("#tracking-error"),result=document.querySelector("#tracking-result");
  error.textContent="";result.hidden=true;button.disabled=true;
  try{
    const data=await request("api.php?route=track",{method:"POST",body:JSON.stringify({orderNumber:form.elements.orderNumber.value,accessCode:form.elements.accessCode.value})});
    result.innerHTML=`<div class="tracking-status"><span>Order status</span><strong>${escapeHtml(deliveryStatusLabel(data.shipping_status))}</strong>${data.tracking_number?`<span>Tracking number</span><strong>${escapeHtml(data.tracking_number)}</strong>`:""}<span>Delivery</span><strong>${escapeHtml(data.shipping_method)} · ${money(data.shippingAmount)}</strong><span>Order number</span><strong>${escapeHtml(data.orderNumber)}</strong></div><h3>In this order</h3>${data.items.map(item=>`<p class="tracking-item"><span>${escapeHtml(item.name)} × ${item.quantity}</span><b>Included</b></p>`).join("")}<p class="tracking-total">Order total <strong>${money(data.total)}</strong></p>`;
    result.hidden=false;
  }catch(error){error.textContent=error.message;}
  finally{button.disabled=false;}
});

document.querySelectorAll("[data-close-dialog]").forEach(button=>button.addEventListener("click",()=>button.closest("dialog").close()));
document.querySelectorAll("dialog").forEach(dialog=>dialog.addEventListener("click",event=>{if(event.target===dialog)dialog.close();}));
document.querySelector(".menu-toggle").addEventListener("click",event=>{
  const nav=document.querySelector("#main-nav");
  const isOpen=nav.classList.toggle("open");
  event.currentTarget.setAttribute("aria-expanded",String(isOpen));
});
document.addEventListener("keydown",event=>{if(event.key==="Escape")closeCart();});
document.querySelector("#newsletter-form").addEventListener("submit",event=>{
  event.preventDefault();
  const email=document.querySelector("#email-input");
  document.querySelector("#newsletter-message").textContent=`Thanks for joining us, ${email.value}.`;
  email.value="";
});

async function initStore(){
  try{
    const csrf=await request("api.php?route=csrf");
    csrfToken=csrf.token;
    const [catalog,serverCart]=await Promise.all([request("api.php?route=products"),request("api.php?route=cart")]);
    products=catalog.products;
    cart=serverCart;
    renderProducts();renderCart();
  }catch(error){
    grid.hidden=true;emptyState.hidden=false;
    emptyState.innerHTML=`<span>◌</span><h3>We’re getting the shop ready</h3><p>${escapeHtml(error.message)} Make sure your PHP database setup is complete.</p>`;
  }
}
initStore();
