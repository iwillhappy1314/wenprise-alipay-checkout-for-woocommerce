(()=>{"use strict";const e=window.React,t=window.wc.wcBlocksRegistry,l=window.wc.wcSettings,i=window.wp.i18n,n=window.wp.htmlEntities,r=(0,i.__)("支付宝支付","wenprise-wc-alipay"),a=({title:e})=>(0,n.decodeEntities)(e)||r,o=({description:e})=>(0,n.decodeEntities)(e||""),s=({logoUrls:t,label:l})=>(0,e.createElement)("div",{style:{display:"flex",flexDirection:"row",gap:"0.5rem",flexWrap:"wrap"}},t.map(((t,i)=>(0,e.createElement)("img",{key:i,src:t,alt:l})))),c=(0,l.getSetting)("wprs-wc-alipay_data",{}),w=a({title:c.title});console.log(c);const p={name:"wprs-wc-alipay",label:(0,e.createElement)((({logoUrls:t,title:l})=>(0,e.createElement)(e.Fragment,null,(0,e.createElement)("div",{style:{display:"flex",flexDirection:"row",gap:"0.5rem"}},(0,e.createElement)("div",null,a({title:l})),(0,e.createElement)(s,{logoUrls:t,label:a({title:l})})))),{logoUrls:c.logo_urls,title:w}),content:(0,e.createElement)(o,{description:c.description}),edit:(0,e.createElement)(o,{description:c.description}),canMakePayment:()=>!0,ariaLabel:w,supports:{features:c.supports}};(0,t.registerPaymentMethod)(p)})();