import{l as y,j as h,dx as F,b7 as L}from"./main-1.0.7.js";import{a4 as s}from"./main-1.0.7-9bcf9658.js";import{T as N}from"./TextField-1.0.7-db52d17f.js";import{I as O}from"./InputAdornment-1.0.7-45f92a3b.js";import"./iconBase-1.0.7-dd244ceb.js";import"./Close-1.0.7-d2499b42.js";const w=({columnName:g,columnValue:E,columnInitialValue:T,columnMetaData:p,storeColumn:e,columnValidation:r,onColumnChange:S,metaData:i,storeForm:f,formMode:x,locale:v})=>{var A,I,_,b;y.debug(g,E,T,p,e,r,i,f,x,v);const U={maxLength:p.character_maximum_length,className:e.classNames,readOnly:x===s.VIEW||x===s.UPDATE&&i.primary_key.includes(g)},d=r!=null&&r.error?r==null?void 0:r.text:v.enterText+" ("+p.character_maximum_length+")",P=()=>L.OUTLINED;return h.jsx(N,{error:r==null?void 0:r.error,label:p.formLabel,value:E??"",required:p.is_nullable==="NO",inputProps:U,InputProps:{startAdornment:(e==null?void 0:e.prefix)&&h.jsx(O,{position:"start",children:(I=(A=e==null?void 0:e.prefix)==null?void 0:A.trimStart())==null?void 0:I.trimEnd()}),endAdornment:(e==null?void 0:e.suffix)&&h.jsx(O,{position:"end",children:(b=(_=e==null?void 0:e.suffix)==null?void 0:_.trimStart())==null?void 0:b.trimEnd()})},helperText:F(e,d),variant:P(),onChange:t=>{let j=t.target.value;t.target.value===""&&(x===s.INSERT||x===s.UPDATE&&f.preserveSpacesOnUpdate===!1||f.preserveSpacesOnUpdate===!0&&T!=="")&&(j=null),S(g,j)},onInvalid:t=>{t.preventDefault()},sx:{"& input":{textAlign:e.alignment}}})};export{w as default};
