/**
 * @name		jQuery Cascdejs plugin
 * @author		zdy
 * @version 	1.0
 */

//首先需要初始化
var xmlDoc;
var TopnodeList;
var CityList;
var citys;
var countyNodes;
var nodeindex = 0;
var childnodeindex = 0;
var new_area = 0;
var open_street = 0;

//获取xml文件
function cascdeInit(na,os, parent_id, v1,v2,v3,v4,v5) {
    id1= ".sel-province";
    id2= ".sel-city";
    id3= ".sel-area";
    id4= ".sel-street";

    new_area = na;
    open_street = os;

    if (v5) {
        id1 += v5;
        id2 += v5;
        id3 += v5;
        id4 += v5;
    }

    //打开xlmdocm文档
    if (new_area == 1) {
        var xmlfile = '/assets/js/dist/area/AreaNew.xml';
    } else {
        var xmlfile = '/assets/js/dist/area/Area.xml';
    }

    xmlDoc = loadXmlFile(xmlfile);
    var dropElement1 = $('#'+parent_id).find(id1)[0];
    var dropElement2 = $('#'+parent_id).find(id2)[0];
    var dropElement3 = $('#'+parent_id).find(id3)[0];
    var dropElement4 = $('#'+parent_id).find(id4)[0];
    RemoveDropDownList(dropElement1);
    RemoveDropDownList(dropElement2);
    RemoveDropDownList(dropElement3);
    RemoveDropDownList(dropElement4);
    if (window.ActiveXObject) {
        TopnodeList = xmlDoc.selectSingleNode("address").childNodes;
    }
    else {
        TopnodeList = xmlDoc.childNodes[0].getElementsByTagName("province");
    }
    if (TopnodeList.length > 0) {
        //省份列表
        var county;
        var province;
        var city;
        for (var i = 0; i < TopnodeList.length; i++) {
            //添加列表项目
            county = TopnodeList[i];
            var option = document.createElement("option");
            option.value = county.getAttribute("name");
            option.text = county.getAttribute("name");
            if (v1 == option.value) {
                option.selected = true;
                nodeindex = i;
            }
            dropElement1.add(option);
        }
        if (TopnodeList.length > 0) {
            //城市列表
            citys = TopnodeList[nodeindex].getElementsByTagName("city");
            for (var i = 0; i < citys.length; i++) {
                var id = dropElement1.options[nodeindex].value;
                //默认为第一个省份的城市
                province = TopnodeList[nodeindex].getElementsByTagName("city");
                var option = document.createElement("option");
                option.value = province[i] .getAttribute("name");
                option.text = province[i].getAttribute("name");
                if (v2 == option.value) {
                    option.selected = true;
                    childnodeindex = i;
                }
                dropElement2.add(option);
            }
            selectcounty(parent_id, v3,v4,v5);
        }
    }
}

/*
//依据省设置城市，县
*/
function selectCity(parent_id,v5) {

    id1= ".sel-province";
    id2= ".sel-city";
    id3= ".sel-area";
    id4= ".sel-street";

    if (v5) {
        id1 += v5;
        id2 += v5;
        id3 += v5;
        id4 += v5;
    }
    var dropElement1 = $('#'+parent_id).find(id1)[0];
    var name = dropElement1.options[dropElement1.selectedIndex].value;
    countyNodes = TopnodeList[dropElement1.selectedIndex];
    var province = $('#'+parent_id).find(id2)[0];
    var city = $('#'+parent_id).find(id3)[0];
    RemoveDropDownList(province);
    RemoveDropDownList(city);
    var citynodes;
    var countycodes;

    if (window.ActiveXObject) {
        citynodes = xmlDoc.selectSingleNode('//address/province [@name="' + name + '"]').childNodes;
    } else {
        citynodes = countyNodes.getElementsByTagName("city")
    }
    if (window.ActiveXObject) {
        countycodes = citynodes[0].childNodes;
    } else {
        countycodes = citynodes[0].getElementsByTagName("county")
    }

    if (citynodes.length > 0) {
        //城市
        for (var i = 0; i < citynodes.length; i++) {
            var provinceNode = citynodes[i];
            var option = document.createElement("option");
            option.value = provinceNode.getAttribute("name");
            option.text = provinceNode.getAttribute("name");
            province.add(option);
        }
        if (countycodes.length > 0) {
            //填充选择省份的第一个城市的县列表
            for (var i = 0; i < countycodes.length; i++) {
                var dropElement2 = $('#'+parent_id).find(id2);
                var dropElement3 = $('#'+parent_id).find(id3);
                //取当天省份下第一个城市列表

                //alert(cityNode.childNodes.length);
                var option = document.createElement("option");
                option.value = countycodes[i].getAttribute("name");
                option.text = countycodes[i].getAttribute("name");
                dropElement3.add(option);
            }
        }
    selectcounty(parent_id, 0,0,v5);
    }
}
/*
//设置县,区
*/
function selectcounty(parent_id, v3,v4,v5) {
    id1= ".sel-province";
    id2= ".sel-city";
    id3= ".sel-area";
    id4= ".sel-street";

    if (v5) {
        id1 += v5;
        id2 += v5;
        id3 += v5;
        id4 += v5;
    }

    var dropElement1 = $('#'+parent_id).find(id1)[0];
    var dropElement2 = $('#'+parent_id).find(id2)[0];
    var name = dropElement2.options[dropElement2.selectedIndex].value;
    var dropElement3 = $('#'+parent_id).find(id3)[0];
    var countys = TopnodeList[dropElement1.selectedIndex].getElementsByTagName("city")[dropElement2.selectedIndex].getElementsByTagName("county");

    if (new_area == 1 && open_street == 1) {
        var city_code = TopnodeList[dropElement1.selectedIndex].getElementsByTagName("city")[dropElement2.selectedIndex].getAttribute("code");
        if (city_code) {
            var left = city_code.substring(0,2);
            var xmlUrl = '/assets/js/dist/area/list/'+left+'/'+city_code+'.xml';
            xmlCityDoc = loadXmlFile(xmlUrl);

            if (window.ActiveXObject) {
                CityList = xmlCityDoc.selectSingleNode("address").childNodes.childNodes;
            } else {
                CityList = xmlCityDoc.childNodes[0].getElementsByTagName("county");
            }
        }
    }

    RemoveDropDownList(dropElement3);
    if (countys.length > 0) {
        for (var i = 0; i < countys.length; i++) {
            var countyNode = countys[i];
            var option = document.createElement("option");
            option.value = countyNode.getAttribute("name");
            option.text = countyNode.getAttribute("name");
            if(v3==option.value){
                option.selected=true;
            }
            dropElement3.add(option);
        }
        if (new_area == 1 && open_street == 1) {
            selectstreet(parent_id,v4,v5);
        }
    }

}

function selectstreet(parent_id,v4,v5) {
    id1= ".sel-province";
    id2= ".sel-city";
    id3= ".sel-area";
    id4= ".sel-street";

    if (v5) {
        id1 += v5;
        id2 += v5;
        id3 += v5;
        id4 += v5;
    }

    var dropElement1 = $('#'+parent_id).find(id1)[0];
    var dropElement2 = $('#'+parent_id).find(id2)[0];
    var name = dropElement2.options[dropElement2.selectedIndex].value;
    var dropElement3 = $('#'+parent_id).find(id3)[0];
    var dropElement4 = $('#'+parent_id).find(id4)[0];

    var area = dropElement3.options[dropElement3.selectedIndex].value;
    var area_code = TopnodeList[dropElement1.selectedIndex].getElementsByTagName("city")[dropElement2.selectedIndex].getElementsByTagName("county")[dropElement3.selectedIndex].getAttribute("code");

    RemoveDropDownList(dropElement4);

    if(CityList && CityList.length>0 && area_code != null) {
        for (var i = 0; i < CityList.length; i++) {
            var county = CityList[i];
            var county_code = county.getAttribute("code");

            if(county_code == area_code){
                var streetlist = county.getElementsByTagName("street");
                for (var m = 0; m < streetlist.length; m++) {
                    var street = streetlist[m];
                    var option = document.createElement("option");
                    option.value = street.getAttribute("name");
                    option.text = street.getAttribute("name");
                    if (v4 == option.value) {
                        option.selected = true;
                        nodeindex = m;
                    }
                    dropElement4.add(option);
                }
            }
        }
    }else{
    	 var option = document.createElement("option");
         option.value = '请选择街道/镇';
         option.text = '请选择街道/镇';
    	dropElement4.add(option);
    }
}

function RemoveDropDownList(obj) {
    if (obj && obj.options) {
        var len = obj.options.length;
        if (len > 0) {
            for (var i = len; i >= 0; i--) {
                obj.remove(i);
            }
        }
    }
}
/*
//读取xml文件
*/
function loadXmlFile(xmlFile) {
    var xmlDom = null;
    if (window.ActiveXObject) {
        xmlDom = new ActiveXObject("Microsoft.XMLDOM");
        xmlDom.async = false;
        xmlDom.load(xmlFile) || xmlDom.loadXML(xmlFile);//如果用的是XML字符串//如果用的是xml文件
    } else if (document.implementation && document.implementation.createDocument) {
        var xmlhttp = new window.XMLHttpRequest();
        xmlhttp.open("GET", xmlFile, false);
        xmlhttp.send(null);
        xmlDom = xmlhttp.responseXML;
    } else {
        xmlDom = null;
    }
    return xmlDom;
}
