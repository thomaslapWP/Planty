import{a as B,g as P,s as y,W as g,B as l,_ as n,L as M,u as S,b as _,h as R,f as H}from"./iconBase-1.0.7-dd244ceb.js";import{j as t,r as m}from"./main-1.0.7.js";import{a as E}from"./FormControlLabel-1.0.7-e4e2218b.js";import{c as h}from"./Close-1.0.7-d2499b42.js";const L=h(t.jsx("path",{d:"M19 5v14H5V5h14m0-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2z"}),"CheckBoxOutlineBlank"),O=h(t.jsx("path",{d:"M19 3H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.11 0 2-.9 2-2V5c0-1.1-.89-2-2-2zm-9 14l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"}),"CheckBox"),U=h(t.jsx("path",{d:"M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-2 10H7v-2h10v2z"}),"IndeterminateCheckBox");function V(o){return P("MuiCheckbox",o)}const N=B("MuiCheckbox",["root","checked","disabled","indeterminate","colorPrimary","colorSecondary","sizeSmall","sizeMedium"]),u=N,F=["checkedIcon","color","icon","indeterminate","indeterminateIcon","inputProps","size","className"],W=o=>{const{classes:e,indeterminate:c,color:a,size:r}=o,s={root:["root",c&&"indeterminate",`color${l(a)}`,`size${l(r)}`]},d=H(s,V,e);return n({},e,d)},w=y(E,{shouldForwardProp:o=>g(o)||o==="classes",name:"MuiCheckbox",slot:"Root",overridesResolver:(o,e)=>{const{ownerState:c}=o;return[e.root,c.indeterminate&&e.indeterminate,e[`size${l(c.size)}`],c.color!=="default"&&e[`color${l(c.color)}`]]}})(({theme:o,ownerState:e})=>n({color:(o.vars||o).palette.text.secondary},!e.disableRipple&&{"&:hover":{backgroundColor:o.vars?`rgba(${e.color==="default"?o.vars.palette.action.activeChannel:o.vars.palette[e.color].mainChannel} / ${o.vars.palette.action.hoverOpacity})`:M(e.color==="default"?o.palette.action.active:o.palette[e.color].main,o.palette.action.hoverOpacity),"@media (hover: none)":{backgroundColor:"transparent"}}},e.color!=="default"&&{[`&.${u.checked}, &.${u.indeterminate}`]:{color:(o.vars||o).palette[e.color].main},[`&.${u.disabled}`]:{color:(o.vars||o).palette.action.disabled}})),T=t.jsx(O,{}),q=t.jsx(L,{}),A=t.jsx(U,{}),D=m.forwardRef(function(e,c){var a,r;const s=S({props:e,name:"MuiCheckbox"}),{checkedIcon:d=T,color:b="primary",icon:z=q,indeterminate:i=!1,indeterminateIcon:x=A,inputProps:I,size:p="medium",className:$}=s,j=_(s,F),C=i?x:z,k=i?x:d,f=n({},s,{color:b,indeterminate:i,size:p}),v=W(f);return t.jsx(w,n({type:"checkbox",inputProps:n({"data-indeterminate":i},I),icon:m.cloneElement(C,{fontSize:(a=C.props.fontSize)!=null?a:p}),checkedIcon:m.cloneElement(k,{fontSize:(r=k.props.fontSize)!=null?r:p}),ownerState:f,ref:c,className:R(v.root,$)},j,{classes:v}))}),X=D;export{X as C};
