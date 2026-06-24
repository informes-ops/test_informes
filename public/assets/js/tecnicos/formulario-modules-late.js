(function(){
  function byId(id){return document.getElementById(id);}
  function setTextContains(sel, from, to){
    document.querySelectorAll(sel).forEach(function(el){
      if(el && el.childNodes && el.childNodes.length===1 && el.textContent.indexOf(from)>=0){ el.textContent = el.textContent.replace(from,to); }
    });
  }
  function fixLabels(){
    const orden = document.querySelector('label[for="orden"]');
    if(orden) orden.textContent = 'N° de reporte';
  }
  window.addEventListener('load', function(){ setTimeout(fixLabels, 120); setTimeout(fixLabels, 900); });
})();

(function(){
  const ZG_CATALOGOS_POR_CONTROLADOR = {"STAR COOL CIM 6":[{"codigo":"818770B","detalle":"2 PIN CONNECTOR (3.81mm/90°) (5 pcs)"},{"codigo":"818270C","detalle":"AIR EXCHANGE MODULE (75CMH)"},{"codigo":"818522F","detalle":"AUXILIARY CONTACT (WHITE DOT 10PCS)"},{"codigo":"811537D","detalle":"BRACKET, EVAPORATOR FAN MOTOR"},{"codigo":"818329A","detalle":"BUTT SPLICE"},{"codigo":"818202B","detalle":"CABLE ADAPTER KIT, FAN MOTOR (10 pcs)"},{"codigo":"815505D","detalle":"CABLE ROOM COVER"},{"codigo":"818561B","detalle":"CABLE SET (X1, X2, X3), CIM 5"},{"codigo":"814247C","detalle":"CABLE, FC (1.0 AND 1.1) TO COMPRESSOR"},{"codigo":"819526B","detalle":"COIL CONDENSER"},{"codigo":"818658B","detalle":"COMPRESSOR"},{"codigo":"818760B","detalle":"CONNECTOR PLUG, SOLENOID COIL (5PCS)"},{"codigo":"818521B","detalle":"CONTACTOR"},{"codigo":"818310C","detalle":"CONTROLLER DOOR, CIM 6"},{"codigo":"868510D","detalle":"CONTROLLER MODULE, CIM 6.0"},{"codigo":"818925A","detalle":"CONTROLLER MODULE, USB CIM 6.2"},{"codigo":"818510E","detalle":"CONTROLLER MODULE, USB CIM 6.2 REMAN"},{"codigo":"815209B","detalle":"COVER PLATE (1715MM), CONDENSER"},{"codigo":"818250E","detalle":"DAMPER, AIR EXCHANGE MODULE"},{"codigo":"881523A","detalle":"DEFROST HEATER, EVAPORATOR (25PCS)"},{"codigo":"814667F","detalle":"ECONOMIZER"},{"codigo":"819737D","detalle":"ECONOMIZER VALVE, R134A"},{"codigo":"881527A","detalle":"EVAPORATOR COIL"},{"codigo":"819543B","detalle":"FAN BLADE, CONDENSER"},{"codigo":"819542C","detalle":"FAN BLADE, EVAPORATOR"},{"codigo":"818965B","detalle":"FREQUENCY CONVERTER 2.1"},{"codigo":"818274C","detalle":"FRONT PART, AIR EXCHANGE MODULE (75 CMH)"},{"codigo":"818530A","detalle":"FUSE 10A"},{"codigo":"818534A","detalle":"FUSE HOLDER 0.4A"},{"codigo":"818656B","detalle":"GASKET, COMPRESSOR STOP VALVE"},{"codigo":"818661B","detalle":"GASKET, SERVICE VALVE LP"},{"codigo":"819501A","detalle":"HIGH PRESSURE SWITCH"},{"codigo":"814644C","detalle":"HINGE PIN"},{"codigo":"889740C","detalle":"HOT GAS VALVE"},{"codigo":"818537A","detalle":"HUMIDITY SENSOR, CIM 6"},{"codigo":"818523C","detalle":"INTERLOCK, CONTACTOR"},{"codigo":"818236B","detalle":"MELT FUSE IT"},{"codigo":"818275A","detalle":"MOTOR, AIR EXCHANGE MODULE"},{"codigo":"818792A","detalle":"MOTOR, CONDENSER FAN"},{"codigo":"818783A","detalle":"MOTOR, EVAPORATOR FAN"},{"codigo":"818525C","detalle":"ON/OFF SWITCH CIM 6"},{"codigo":"881550A","detalle":"PLUG, EVAPORATOR SERVICE HOLE"},{"codigo":"818905A","detalle":"POWER MEASUREMENT MODULE, CIM 6.2"},{"codigo":"819504D","detalle":"PRESSURE TRANSMITTER HP NSK"},{"codigo":"819503D","detalle":"PRESSURE TRANSMITTER LP NSK"},{"codigo":"814540B","detalle":"RECEIVER"},{"codigo":"818739A","detalle":"RECEIVER, WATER COOLED CONDENSER"},{"codigo":"818276A","detalle":"SENSOR AIR EXCHANGE MODULE"},{"codigo":"818623C","detalle":"SERVICE VALVE, COMPRESSOR LP"},{"codigo":"818235B","detalle":"SIGHT GLASS RECEIVER KIT"},{"codigo":"818553A","detalle":"SOLENOID COIL 14W 24VDC CIM 5"},{"codigo":"818554A","detalle":"SOLENOID COIL 18W 24VDC CIM 5"},{"codigo":"886554B","detalle":"SOLENOID COIL 11W 24VAC"},{"codigo":"814541C","detalle":"SQUARE FAN GRILLE, CONDENSER"},{"codigo":"819500C","detalle":"STOP VALVE RECEIVER"},{"codigo":"818940A","detalle":"TEMPERATURE SENSOR 0.35M"},{"codigo":"818943B","detalle":"TEMPERATURE SENSOR INCL. CABLE GLAND (3M)"},{"codigo":"818639B","detalle":"TERMINAL BLOCK, COMPRESSOR"},{"codigo":"818518C","detalle":"TRANSFORMER 105 VA CIM 6"},{"codigo":"886513A","detalle":"USER PANEL, CIM 6.1"},{"codigo":"INDND0078","detalle":"ACEITE AFLOJATODO"},{"codigo":"INDND0411","detalle":"ACEITE POLYOLESTER BVA"},{"codigo":"INDND3242","detalle":"BROCA DE COBALTO HSS 3/16"},{"codigo":"INDND0259","detalle":"CABLE VULCANIZADO 4 X10"},{"codigo":"RNDND0254","detalle":"CINTA AISLANTE 3M"},{"codigo":"INDND0432","detalle":"CINTA FOAM 1/8 X 2 X 9.14M"},{"codigo":"INDND0433","detalle":"CINTILLO DE AMARRE 150MM"},{"codigo":"INDND0434","detalle":"CINTILLO DE AMARRE 250MM"},{"codigo":"INDND1008","detalle":"CINTILLO DE AMARRE 350MM"},{"codigo":"INDND1911","detalle":"EMPAQUE ASBESTO 1-32 X 1.50 X 1.50 M"},{"codigo":"INDND0126","detalle":"ESTAÑO 0.8"},{"codigo":"INDND2552","detalle":"FILTRO SECADOR QDM-164 1/2 - QUALITY"},{"codigo":"INDND0024","detalle":"FILTRO SEC. FIJO DE 1/2 FLARE - EK 164 STD"},{"codigo":"INDND2905","detalle":"FORMADOR EMPAQUETADURA AVIACION 3H"},{"codigo":"INDND2237","detalle":"FUNDENTE"},{"codigo":"INDND1545","detalle":"MINI FUSIBLE DE VIDRIO 15 AMP"},{"codigo":"INDND1542","detalle":"FUSIBLE DE VIDRIO 10 AMP"},{"codigo":"RNDND0318","detalle":"FUSIBLE DE VIDRIO 20 AMP"},{"codigo":"INDND0120","detalle":"GAS REFRIGERANTE R-134A X 13.60KG"},{"codigo":"RNDND0438","detalle":"JABON LIQUIDO"},{"codigo":"INDND0016","detalle":"LIJA FIERRO #40 ASA"},{"codigo":"INDND0079","detalle":"LIMPIA CONTACTO"},{"codigo":"INDND0086","detalle":"NITROGENO INDUSTRIAL 10 M3"},{"codigo":"INDND3074","detalle":"MANGA TERMOCONTRAIBLE 15MM"},{"codigo":"INDND3322","detalle":"MANGA TERMOCONTRAIBLE 20MM"},{"codigo":"INDND3075","detalle":"MANGA TERMOCONTRAIBLE 3MM"},{"codigo":"INDND2974","detalle":"MANGA TERMOCONTRAIBLE 5MM"},{"codigo":"INDND1555","detalle":"PERNO HEX. RC. INOX 304 M16X50"},{"codigo":"INDND0107","detalle":"PINTURA EN SPRAY NEGRO"},{"codigo":"INDND0173","detalle":"REMACHE POP DE ALUMINIO 3/16X1/2"},{"codigo":"INDND2768","detalle":"RODAJE 6201 2RSH/C3"},{"codigo":"INDND0121","detalle":"SOLDADURA DE PLATA 0%"},{"codigo":"INDND2553","detalle":"SOLDADURA DE PLATA 15%"},{"codigo":"INDND2901","detalle":"SOLDADURA DE PLATA 5%"},{"codigo":"INDND0265","detalle":"TERMINAL OJO 5.5-5 / 12-10"},{"codigo":"INDND5576","detalle":"TERMINAL TUBULAR SOBREMOLDEADO ROJO 4MM 12AWG"},{"codigo":"INDND2711","detalle":"TERMINAL TUBULAR SOBREMOLDEADO GRIS 4MM 12AWG"},{"codigo":"INDND2936","detalle":"TERMINAL TUBULAR SOBREMOLDEADO NEGRO 6MM 10AWG"},{"codigo":"INDND0017","detalle":"TRAPO INDUSTRIAL SUELTO"},{"codigo":"INDND0087","detalle":"SOLVENTE DIELECTRICO SDL-25"},{"codigo":"INDND1412","detalle":"VALVULA DE ACCESO 1/4 X 7 CM"}],"STAR COOL CIM 5":[{"codigo":"818270C","detalle":"AIR EXCHANGE MODULE (75CMH)"},{"codigo":"819747B","detalle":"AIR RELEASE VALVE, RECEIVER"},{"codigo":"818522F","detalle":"AUXILIARY CONTACT (WHITE DOT 10PCS)"},{"codigo":"818522C","detalle":"AUXILIARY CONTACT"},{"codigo":"818536B","detalle":"BATTERY PACK CIM5"},{"codigo":"811537D","detalle":"BRACKET, EVAPORATOR FAN MOTOR"},{"codigo":"818202B","detalle":"CABLE ADAPTER KIT, FAN MOTOR"},{"codigo":"815505D","detalle":"CABLE ROOM COVER"},{"codigo":"818561B","detalle":"CABLE SET (X1, X2, X3), CIM 5"},{"codigo":"814247C","detalle":"CABLE, FC (1.0 AND 1.1) TO COMPRESSOR"},{"codigo":"819526B","detalle":"COIL CONDENSER"},{"codigo":"818658B","detalle":"COMPRESSOR"},{"codigo":"818521B","detalle":"CONTACTOR"},{"codigo":"818310B","detalle":"CONTROLLER DOOR, CIM 5"},{"codigo":"818320B","detalle":"CONTROLLER DOOR, COMPLETE CIM 5"},{"codigo":"818512A","detalle":"CONTROLLER MODULE, CA"},{"codigo":"868255C","detalle":"CONTROLLER MODULE, CIM 5"},{"codigo":"818255C","detalle":"CONTROLLER MODULE, CIM 5"},{"codigo":"818209D","detalle":"COVER PLATE (2100MM) CONDENSER SCI"},{"codigo":"818250E","detalle":"DAMPER, AIR EXCHANGE MODULE"},{"codigo":"811522B","detalle":"DEFROST HEATER ELEMENT, TRAY"},{"codigo":"818515A","detalle":"DISPLAY PCB, CIM 5"},{"codigo":"814667F","detalle":"ECONOMIZER"},{"codigo":"819737D","detalle":"ECONOMIZER VALVE, R134A"},{"codigo":"881527A","detalle":"EVAPORATOR COIL"},{"codigo":"819543B","detalle":"FAN BLADE, CONDENSER"},{"codigo":"819542C","detalle":"FAN BLADE, EVAPORATOR"},{"codigo":"819506A","detalle":"FILTER DRYER, R134A AND R513A"},{"codigo":"818738A","detalle":"FILTER DRYER, R134A AND R513A (12 PCS)"},{"codigo":"818965B","detalle":"FREQUENCY CONVERTER 2.1"},{"codigo":"818274C","detalle":"FRONT PART, AIR EXCHANGE MODULE (75 CMH)"},{"codigo":"818656B","detalle":"GASKET, COMPRESSOR STOP VALVE"},{"codigo":"818661B","detalle":"GASKET, SERVICE VALVE LP"},{"codigo":"819501A","detalle":"HIGH PRESSURE SWITCH"},{"codigo":"889740C","detalle":"HOT GAS VALVE"},{"codigo":"819740B","detalle":"HOT GAS VALVE, CIM 5"},{"codigo":"818551A","detalle":"HUMIDITY SENSOR"},{"codigo":"814571B","detalle":"INSULATION, ECONOMIZER"},{"codigo":"818523C","detalle":"INTERLOCK, CONTACTOR"},{"codigo":"818527B","detalle":"KEY PAD CIM 5"},{"codigo":"818517A","detalle":"LED PCB, CIM 5"},{"codigo":"818906A","detalle":"MAIN CIRCUIT BREAKER, CIM 5"},{"codigo":"818236B","detalle":"MELT FUSE IT"},{"codigo":"818792A","detalle":"MOTOR, CONDENSER FAN"},{"codigo":"818783A","detalle":"MOTOR, EVAPORATOR FAN"},{"codigo":"814538D","detalle":"MOUNTING RING, FILTER"},{"codigo":"818525B","detalle":"ON/OFF CIM5"},{"codigo":"818652A","detalle":"PERMANENT MAGNET"},{"codigo":"881550A","detalle":"PLUG, EVAPORATOR SERVICE HOLE"},{"codigo":"819541C","detalle":"PLUG, WATER INLET COUPLING"},{"codigo":"819540C","detalle":"PLUG, WATER OUTLET COUPLING"},{"codigo":"818511B","detalle":"POWER MEASUREMENT PCB, CIM 5"},{"codigo":"819503D","detalle":"PRESSURE TRANSMITTER LP NSK"},{"codigo":"819504D","detalle":"PRESSURE TRANSMITTER HP DST"},{"codigo":"814540B","detalle":"RECEIVER"},{"codigo":"818739A","detalle":"RECEIVER, WATER COOLED CONDENSER"},{"codigo":"819693D","detalle":"SCREW, CONTROLLER DOOR CIM 6"},{"codigo":"818276A","detalle":"SENSOR AIR EXCHANGE MODULE"},{"codigo":"818675B","detalle":"SERVICE KIT, HOT GAS VALVE CIM 5"},{"codigo":"818623C","detalle":"SERVICE VALVE, COMPRESSOR LP"},{"codigo":"818235B","detalle":"SIGHT GLASS KIT, RECEIVER"},{"codigo":"818554A","detalle":"SOLENOID COIL 18W 24VDC CIM 5"},{"codigo":"818553A","detalle":"SOLENOID COIL 14W 24VDC CIM 5"},{"codigo":"886554B","detalle":"SOLENOID COIL 11W 24VAC"},{"codigo":"818526C","detalle":"TERMINAL BLOCK PCB, CIM 5"},{"codigo":"818639B","detalle":"TERMINAL BLOCK, COMPRESSOR"},{"codigo":"818676B","detalle":"TOOL, HOT GAS VALVE"},{"codigo":"818518B","detalle":"TRANSFORMER 145 VA, CIM 5"},{"codigo":"818267B","detalle":"WING SCREW KIT"},{"codigo":"INDND0078","detalle":"ACEITE AFLOJATODO"},{"codigo":"INDND0411","detalle":"ACEITE POLYOLESTER BVA"},{"codigo":"INDND3242","detalle":"BROCA DE COBALTO HSS 3/16"},{"codigo":"INDND0259","detalle":"CABLE VULCANIZADO 4 X10"},{"codigo":"RNDND0254","detalle":"CINTA AISLANTE 3M"},{"codigo":"INDND0432","detalle":"CINTA FOAM 1/8 X 2 X 9.14M"},{"codigo":"INDND0433","detalle":"CINTILLO DE AMARRE 150MM"},{"codigo":"INDND0434","detalle":"CINTILLO DE AMARRE 250MM"},{"codigo":"INDND1008","detalle":"CINTILLO DE AMARRE 350MM"},{"codigo":"INDND1911","detalle":"EMPAQUE ASBESTO 1-32 X 1.50 X 1.50 M"},{"codigo":"INDND0126","detalle":"ESTAÑO 0.8"},{"codigo":"INDND2552","detalle":"FILTRO SECADOR QDM-164 1/2 - QUALITY"},{"codigo":"INDND0024","detalle":"FILTRO SEC. FIJO DE 1/2 FLARE - EK 164 STD"},{"codigo":"INDND2905","detalle":"FORMADOR EMPAQUETADURA AVIACION 3H"},{"codigo":"INDND2237","detalle":"FUNDENTE"},{"codigo":"INDND1545","detalle":"MINI FUSIBLE DE VIDRIO 15 AMP"},{"codigo":"INDND1542","detalle":"FUSIBLE DE VIDRIO 10 AMP"},{"codigo":"RNDND0318","detalle":"FUSIBLE DE VIDRIO 20 AMP"},{"codigo":"INDND0120","detalle":"GAS REFRIGERANTE R-134A X 13.60KG"},{"codigo":"RNDND0438","detalle":"JABON LIQUIDO"},{"codigo":"INDND0016","detalle":"LIJA FIERRO #40 ASA"},{"codigo":"INDND0079","detalle":"LIMPIA CONTACTO"},{"codigo":"INDND0086","detalle":"NITROGENO INDUSTRIAL 10 M3"},{"codigo":"INDND3074","detalle":"MANGA TERMOCONTRAIBLE 15MM"},{"codigo":"INDND3322","detalle":"MANGA TERMOCONTRAIBLE 20MM"},{"codigo":"INDND3075","detalle":"MANGA TERMOCONTRAIBLE 3MM"},{"codigo":"INDND2974","detalle":"MANGA TERMOCONTRAIBLE 5MM"},{"codigo":"INDND1555","detalle":"PERNO HEX. RC. INOX 304 M16X50"},{"codigo":"INDND0107","detalle":"PINTURA EN SPRAY NEGRO"},{"codigo":"INDND0173","detalle":"REMACHE POP DE ALUMINIO 3/16X1/2"},{"codigo":"INDND2768","detalle":"RODAJE 6201 2RSH/C3"},{"codigo":"INDND0121","detalle":"SOLDADURA DE PLATA 0%"},{"codigo":"INDND2553","detalle":"SOLDADURA DE PLATA 15%"},{"codigo":"INDND2901","detalle":"SOLDADURA DE PLATA 5%"},{"codigo":"INDND0265","detalle":"TERMINAL OJO 5.5-5 / 12-10"},{"codigo":"INDND5576","detalle":"TERMINAL TUBULAR SOBREMOLDEADO ROJO 4MM 12AWG"},{"codigo":"INDND2711","detalle":"TERMINAL TUBULAR SOBREMOLDEADO GRIS 4MM 12AWG"},{"codigo":"INDND2936","detalle":"TERMINAL TUBULAR SOBREMOLDEADO NEGRO 6MM 10AWG"},{"codigo":"INDND0017","detalle":"TRAPO INDUSTRIAL SUELTO"},{"codigo":"INDND0087","detalle":"SOLVENTE DIELECTRICO SDL-25"},{"codigo":"INDND1412","detalle":"VALVULA DE ACCESO 1/4 X 7 CM"}],"TK MP5000":[{"codigo":"672454","detalle":"COIL - CONDENSER (ALUMINUM FINS)"},{"codigo":"781924","detalle":"FAN - CONDENSER"},{"codigo":"1040858","detalle":"MOTOR - CONDENSER FAN"},{"codigo":"970599","detalle":"HOUSING - EVAPORATOR (2 FANS)"},{"codigo":"427333","detalle":"SENSOR - RETURN AIR"},{"codigo":"782096","detalle":"FAN - EVAPORATOR 355MM 7 BLADES"},{"codigo":"941887","detalle":"BRACKET - MOTOR"},{"codigo":"1040894","detalle":"MOTOR - FAN"},{"codigo":"673471","detalle":"COIL - EVAPORATOR"},{"codigo":"427334","detalle":"SENSOR - DEFROST"},{"codigo":"422659","detalle":"SENSOR - HUMIDITY"},{"codigo":"427338","detalle":"SENSOR - CO2 RS485"},{"codigo":"420374","detalle":"CABLE - SUPPLY RS485"},{"codigo":"612477","detalle":"TUBE - VALVE TO COIL"},{"codigo":"672787","detalle":"TANK - RECEIVER STANDARD"},{"codigo":"610786","detalle":"DEHYDRATOR"},{"codigo":"671889","detalle":"HEAT EXCHANGER - ECONOMIZER"},{"codigo":"618684","detalle":"VALVE - SOLENOID VAPOR INJECTION"},{"codigo":"415460","detalle":"COIL - VALVE"},{"codigo":"600731","detalle":"KIT - TXV EXPANSION VALVE"},{"codigo":"612465","detalle":"VALVE - BALL"},{"codigo":"617758","detalle":"VALVE PWM"},{"codigo":"421423","detalle":"SWITCH - LPCO"},{"codigo":"672853","detalle":"TANK - RECEIVER WITH SHUT-OFF VALVE"},{"codigo":"425968","detalle":"TRANSDUCER - SUCTION"},{"codigo":"610443","detalle":"VALVE - EXPANSION"},{"codigo":"1020795","detalle":"COMPRESSOR - SCROLL"},{"codigo":"919021","detalle":"COVER - TERMINAL BOX"},{"codigo":"401377","detalle":"KIT - THERMISTOR"},{"codigo":"414004","detalle":"SWITCH - HPCO"},{"codigo":"612118","detalle":"VALVE - SUCTION"},{"codigo":"612119","detalle":"VALVE - DISCHARGE"},{"codigo":"335215","detalle":"GASKET - VALVE SERVICE"},{"codigo":"400782","detalle":"KIT - POWER CORD"},{"codigo":"401044","detalle":"SENSOR KIT DEFROST/AMBIENT/RETURN/SUPPLY/COIL"},{"codigo":"451992","detalle":"CABLE - POWER 19.2 METERS"},{"codigo":"452889","detalle":"HEATER 1360W"},{"codigo":"453031","detalle":"BASE - CONTROL BOX MP-5000"},{"codigo":"413595","detalle":"SWITCH - ON/OFF"},{"codigo":"426427","detalle":"TRANSFORMER"},{"codigo":"426424","detalle":"BATTERY - MP-5000"},{"codigo":"426423","detalle":"CONTROLLER - MP-5000"},{"codigo":"427238","detalle":"BUSBAR COMB 63A 3 TAP-OFFS"},{"codigo":"427239","detalle":"BUSBAR COMB 63A 4 TAP-OFFS"},{"codigo":"423820","detalle":"CONTACTOR AC LC1D 3P 25A"},{"codigo":"426428","detalle":"TRANSFORMER CURRENT MP-5000"},{"codigo":"415104","detalle":"BREAKER CIRCUIT 25A"},{"codigo":"940841","detalle":"DOOR - CONTROLLER MP-5000"},{"codigo":"426430","detalle":"KEYPAD - CONTROLLER"},{"codigo":"427072","detalle":"DISPLAY - LARGE"},{"codigo":"426752","detalle":"MODULE - MP-5000"},{"codigo":"1021428","detalle":"COMPRESSOR ASSEMBLY WITH MOTOR"},{"codigo":"221473","detalle":"FILTER - COMPRESSOR SUCTION"},{"codigo":"941897","detalle":"COVER - COMPRESSOR"},{"codigo":"427160","detalle":"SENSOR - O2 RS485"},{"codigo":"427161","detalle":"SENSOR - CO2 RS485"},{"codigo":"918252TKA","detalle":"VENT - AIR COMPLETE"},{"codigo":"929522","detalle":"BRACKET - AIR VENT"},{"codigo":"417238TKA","detalle":"ACTUATOR"},{"codigo":"925687","detalle":"DOOR - AFAM"},{"codigo":"937326","detalle":"GRILLE - FRESH AIR"},{"codigo":"925661","detalle":"LABEL - AFAM DOOR POSITION"},{"codigo":"INDND0411","detalle":"ACEITE POLYOLESTER"},{"codigo":"INDND0078","detalle":"AFLOJATODO"},{"codigo":"INDND4464","detalle":"AGENTE LIMPIADOR DE SISTEMAS OPTEON SF FLUSH X 4.54 KG"},{"codigo":"INDND3876","detalle":"ANILLO DE TEFLON 1/16 D 15 X 10 MM"},{"codigo":"INDND3565","detalle":"ARANDELA DE PRESION INOX M6"},{"codigo":"INDND1391","detalle":"ARANDELA PLANA INOX 1/4"},{"codigo":"INDND2543","detalle":"BOMBA DE VACIO CPS VP6D 1/2HP"},{"codigo":"INDND0259","detalle":"CABLE VULCANIZADO 4X10"},{"codigo":"RNDND0254","detalle":"CINTA AISLANTE 3M"},{"codigo":"INDND0432","detalle":"CINTA FOAM 1/8 X 2 X 9.4 M"},{"codigo":"INDND4185","detalle":"CINTA PARA DUCTO 10MX48MM"},{"codigo":"INDND2786","detalle":"CINTA VULCANIZANTE SCOTCH 23 3/4"},{"codigo":"INDND0434","detalle":"CINTILLO DE AMARRE 250MM"},{"codigo":"INDND1008","detalle":"CINTILLO DE AMARRE 350MM"},{"codigo":"INDND1718","detalle":"CONTACTOR SCHNEIDER 32A 440V LC1D32R7"},{"codigo":"INDND4864","detalle":"CONTACTOR TESYS DECA 3P AC-3 12A BOBINA 24VAC LC1D12B7"},{"codigo":"INDND1911","detalle":"EMPAQUE ASBESTO 1-32 X 1.50 X 1.50 M"},{"codigo":"RNDND0198","detalle":"ENCHUFE 32AMP 3P+T 440V ROJO 3H IP67"},{"codigo":"INDND0126","detalle":"ESTAÑO 0.8"},{"codigo":"818738A","detalle":"FILTER DRYER, R134A AND R513A (12 PCS)"},{"codigo":"RNDND0318","detalle":"FUSIBLE DE VIDRIO 20 AMP"},{"codigo":"INDND2144","detalle":"GAS MAP PRO"},{"codigo":"INDND0022","detalle":"GAS REFRIGERANTE R-404A X 10.90KG"},{"codigo":"RNDND0438","detalle":"JABON LIQUIDO"},{"codigo":"INDND1184","detalle":"LIJA FIERRO #100"},{"codigo":"INDND0016","detalle":"LIJA FIERRO #40"},{"codigo":"INDND0079","detalle":"LIMPIA CONTACTO"},{"codigo":"INDND2300","detalle":"MANGA TERMOCONTRAIBLE 25MM"},{"codigo":"INDND3075","detalle":"MANGA TERMOCONTRAIBLE 3MM"},{"codigo":"INDND2974","detalle":"MANGA TERMOCONTRAIBLE 5MM"},{"codigo":"INDND0738","detalle":"MANGUERA CORRUGADA 1/2"},{"codigo":"INDND0279","detalle":"MANGUERA CORRUGADA 3/8"},{"codigo":"INDND2111","detalle":"MANGUERA CORRUGADA DE 1 PULGADA"},{"codigo":"INDND4520","detalle":"ORING VITON 3-023"},{"codigo":"INDND3649","detalle":"ORING VITON 2-014"},{"codigo":"INDND1104","detalle":"PEGAMENTO SUPERFLEX INDUSTRIAL"},{"codigo":"INDND1417","detalle":"PERNO HEX ZINC 1/4 X 1"},{"codigo":"INDND0107","detalle":"PINTURA EN SPRAY NEGRO"},{"codigo":"INDND2789","detalle":"PRENSA ESTOPA 1 NPT"},{"codigo":"INDND2838","detalle":"PRENSA ESTOPA 3/8 PG11"},{"codigo":"INDND3078","detalle":"RELE PROTECTOR DE FASE TIPO GALLETA GRV8-03"},{"codigo":"INDND0173","detalle":"REMACHE POP DE ALUMINIO 3/16X1/2"},{"codigo":"INDND2265","detalle":"RIEL DIN PERFORADO"},{"codigo":"INDND0260","detalle":"RODAMIENTO 6203"},{"codigo":"INDND0081","detalle":"RODAMIENTO 6205"},{"codigo":"INDND0121","detalle":"SOLDADURA DE PLATA 0%"},{"codigo":"INDND2553","detalle":"SOLDADURA DE PLATA 15%"},{"codigo":"INDND2901","detalle":"SOLDADURA DE PLATA 5%"},{"codigo":"INDND1962","detalle":"TERMINAL AISLADO TIPO BALA HEMBRA AZUL"},{"codigo":"INDND1097","detalle":"TERMINAL AISLADO TIPO BALA MACHO AZUL"},{"codigo":"INDND0265","detalle":"TERMINAL OJO 5.5-5 / 12-10"},{"codigo":"INDND0885","detalle":"TERMINAL OJO VF5.5-6S 1/4"},{"codigo":"INDND2711","detalle":"TERMINAL TUBULAR SOBREMOLDEADO GRIS 4MM 12AWG"},{"codigo":"INDND2936","detalle":"TERMINAL TUBULAR SOBREMOLDEADO NEGRO 6MM 10AWG"},{"codigo":"INDND0017","detalle":"TRAPO INDUSTRIAL SUELTO"},{"codigo":"RNDND0802","detalle":"TUBERIA DE COBRE 1/2"},{"codigo":"INDND0754","detalle":"TUBERIA DE COBRE 1/4"},{"codigo":"INDND0357","detalle":"TUBERIA DE COBRE 3/8"},{"codigo":"RNDND0440","detalle":"TUBO DE COBRE 1/8 X 15M"},{"codigo":"INDND0169","detalle":"TUERCA HEXAGONAL 1/4 ZINCADO"},{"codigo":"INDND1536","detalle":"UNION SOLDABLE 1/4"}],"TK MP4000":[{"codigo":"417238TKA","detalle":"ACTUATOR"},{"codigo":"918252","detalle":"AIR VENT"},{"codigo":"418717","detalle":"BATTERY LITHIUM MP-4000"},{"codigo":"413596","detalle":"BLOCK - TERMINAL 4 POLE"},{"codigo":"413598","detalle":"BLOCK - TERMINAL 8 POLE"},{"codigo":"918466","detalle":"BRACKET MOTOR"},{"codigo":"RNDND0724","detalle":"BREAKER CIRCUIT 25A"},{"codigo":"418716","detalle":"CABLE SERIAL CM-4000A0 / PM4000"},{"codigo":"INDND5049","detalle":"CABLE SUPPLY 420374"},{"codigo":"91-9331","detalle":"CHANNEL - FRESH AIR"},{"codigo":"671923","detalle":"COIL EVAPORATOR"},{"codigo":"415460","detalle":"COIL VALVE LIQ"},{"codigo":"INDND1604","detalle":"COMPRESSOR - SCROLL"},{"codigo":"69NT4320220","detalle":"CONDENSER COIL"},{"codigo":"421636","detalle":"CONNECTOR 10-PIN J2/J17"},{"codigo":"412446","detalle":"CONTACTOR 25A"},{"codigo":"RNDND0064","detalle":"CONTACTOR 30 AMP"},{"codigo":"100043106","detalle":"CONTACTOR 12AMP"},{"codigo":"452295","detalle":"CONTROLLER MP4000"},{"codigo":"418718","detalle":"COVER EXPANSION BOARD"},{"codigo":"937354","detalle":"DECAL R404A"},{"codigo":"418723","detalle":"DOOR FRONT MP-4000 WHITE"},{"codigo":"610156","detalle":"DRIER UNIVERSAL CONTAINER"},{"codigo":"818738A","detalle":"FILTER DRYER, R134A AND R513A (12 PCS)"},{"codigo":"781683","detalle":"EVAPORATOR FAN"},{"codigo":"78-1684","detalle":"FAN - CONDENSER"},{"codigo":"781924","detalle":"FAN CONDENSER ASSEMBLY"},{"codigo":"669842","detalle":"FITTING FOR LPCO"},{"codigo":"559485","detalle":"FLATWASHER"},{"codigo":"RNDND0624","detalle":"FUSE HOLDER BLK MP4000"},{"codigo":"332510","detalle":"GASKET - VALVE PLATE"},{"codigo":"332805","detalle":"GASKET DISCHARGE"},{"codigo":"988244","detalle":"GRILLE - EVAPORATOR"},{"codigo":"452889","detalle":"HEATER ELEMENT 1360W BROWN"},{"codigo":"45-2451","detalle":"HEATER ELEMENT 2000W"},{"codigo":"3504979","detalle":"HEATER ELEMENT 750W 230V"},{"codigo":"422659","detalle":"HUMIDITY SENSOR"},{"codigo":"INDND4844","detalle":"KIT - POWER CORD"},{"codigo":"401044","detalle":"KIT SENSOR MP4000"},{"codigo":"900331TKA","detalle":"KIT SPACER FAN"},{"codigo":"INDND4843","detalle":"KIT THERMISTOR THK"},{"codigo":"INDND1609","detalle":"KIT TXV ECONOMIZER"},{"codigo":"420353","detalle":"MODULE - AFAM+"},{"codigo":"418719","detalle":"MODULE POWER MP4000"},{"codigo":"104-759","detalle":"MOTOR CONDENSADOR TK"},{"codigo":"104691","detalle":"MOTOR EVAPORADOR"},{"codigo":"47225","detalle":"MP4000 CONTROL BOX"},{"codigo":"330727","detalle":"O RING"},{"codigo":"927635","detalle":"RAIL - DIN"},{"codigo":"421595","detalle":"SENSOR CO2 RS485"},{"codigo":"RNDND0562","detalle":"SENSOR USDA 2.5MM"},{"codigo":"781737","detalle":"SHROUD FAN"},{"codigo":"414004","detalle":"SWITCH HPCO"},{"codigo":"INDND1606","detalle":"SWITCH LPCO"},{"codigo":"418763","detalle":"TRANSFORMER MP4000"},{"codigo":"618179","detalle":"TX VALVE ECONOMIZER"},{"codigo":"669900","detalle":"VALVE EXPANSION ECONOMIZER"},{"codigo":"RNDND0130","detalle":"VALVE EXPANSION"},{"codigo":"612470","detalle":"VALVE SOLENOID"},{"codigo":"617758","detalle":"VALVE DIGITAL"},{"codigo":"INDND0411","detalle":"ACEITE POLYOLESTER"},{"codigo":"INDND0078","detalle":"AFLOJATODO"},{"codigo":"INDND4464","detalle":"AGENTE LIMPIADOR DE SISTEMAS OPTEON SF FLUSH X 4.54 KG"},{"codigo":"INDND3876","detalle":"ANILLO DE TEFLON 1/16 D 15 X 10 MM"},{"codigo":"INDND3565","detalle":"ARANDELA DE PRESION INOX M6"},{"codigo":"INDND1391","detalle":"ARANDELA PLANA INOX 1/4"},{"codigo":"INDND2543","detalle":"BOMBA DE VACIO CPS VP6D 1/2HP"},{"codigo":"INDND0259","detalle":"CABLE VULCANIZADO 4X10"},{"codigo":"RNDND0254","detalle":"CINTA AISLANTE 3M"},{"codigo":"INDND0432","detalle":"CINTA FOAM 1/8 X 2 X 9.4 M"},{"codigo":"INDND4185","detalle":"CINTA PARA DUCTO 10MX48MM"},{"codigo":"INDND2786","detalle":"CINTA VULCANIZANTE SCOTCH 23 3/4"},{"codigo":"INDND0434","detalle":"CINTILLO DE AMARRE 250MM"},{"codigo":"INDND1008","detalle":"CINTILLO DE AMARRE 350MM"},{"codigo":"INDND1718","detalle":"CONTACTOR SCHNEIDER 32A 440V LC1D32R7"},{"codigo":"INDND4864","detalle":"CONTACTOR TESYS DECA 3P AC-3 12A BOBINA 24VAC LC1D12B7"},{"codigo":"INDND1911","detalle":"EMPAQUE ASBESTO 1-32 X 1.50 X 1.50 M"},{"codigo":"RNDND0198","detalle":"ENCHUFE 32AMP 3P+T 440V ROJO 3H IP67"},{"codigo":"INDND0126","detalle":"ESTAÑO 0.8"},{"codigo":"RNDND0318","detalle":"FUSIBLE DE VIDRIO 20 AMP"},{"codigo":"INDND2144","detalle":"GAS MAP PRO"},{"codigo":"INDND0022","detalle":"GAS REFRIGERANTE R-404A X 10.90KG"},{"codigo":"RNDND0438","detalle":"JABON LIQUIDO"},{"codigo":"INDND1184","detalle":"LIJA FIERRO #100"},{"codigo":"INDND0016","detalle":"LIJA FIERRO #40"},{"codigo":"INDND0079","detalle":"LIMPIA CONTACTO"},{"codigo":"INDND2300","detalle":"MANGA TERMOCONTRAIBLE 25MM"},{"codigo":"INDND3075","detalle":"MANGA TERMOCONTRAIBLE 3MM"},{"codigo":"INDND2974","detalle":"MANGA TERMOCONTRAIBLE 5MM"},{"codigo":"INDND0738","detalle":"MANGUERA CORRUGADA 1/2"},{"codigo":"INDND0279","detalle":"MANGUERA CORRUGADA 3/8"},{"codigo":"INDND2111","detalle":"MANGUERA CORRUGADA DE 1 PULGADA"},{"codigo":"INDND4520","detalle":"ORING VITON 3-023"},{"codigo":"INDND3649","detalle":"ORING VITON 2-014"},{"codigo":"INDND1104","detalle":"PEGAMENTO SUPERFLEX INDUSTRIAL"},{"codigo":"INDND1417","detalle":"PERNO HEX ZINC 1/4 X 1"},{"codigo":"INDND0107","detalle":"PINTURA EN SPRAY NEGRO"},{"codigo":"INDND2789","detalle":"PRENSA ESTOPA 1 NPT"},{"codigo":"INDND2838","detalle":"PRENSA ESTOPA 3/8 PG11"},{"codigo":"INDND3078","detalle":"RELE PROTECTOR DE FASE TIPO GALLETA GRV8-03"},{"codigo":"INDND0173","detalle":"REMACHE POP DE ALUMINIO 3/16X1/2"},{"codigo":"INDND2265","detalle":"RIEL DIN PERFORADO"},{"codigo":"INDND0260","detalle":"RODAMIENTO 6203"},{"codigo":"INDND0081","detalle":"RODAMIENTO 6205"},{"codigo":"INDND0121","detalle":"SOLDADURA DE PLATA 0%"},{"codigo":"INDND2553","detalle":"SOLDADURA DE PLATA 15%"},{"codigo":"INDND2901","detalle":"SOLDADURA DE PLATA 5%"},{"codigo":"INDND1962","detalle":"TERMINAL AISLADO TIPO BALA HEMBRA AZUL"},{"codigo":"INDND1097","detalle":"TERMINAL AISLADO TIPO BALA MACHO AZUL"},{"codigo":"INDND0265","detalle":"TERMINAL OJO 5.5-5 / 12-10"},{"codigo":"INDND0885","detalle":"TERMINAL OJO VF5.5-6S 1/4"},{"codigo":"INDND2711","detalle":"TERMINAL TUBULAR SOBREMOLDEADO GRIS 4MM 12AWG"},{"codigo":"INDND2936","detalle":"TERMINAL TUBULAR SOBREMOLDEADO NEGRO 6MM 10AWG"},{"codigo":"INDND0017","detalle":"TRAPO INDUSTRIAL SUELTO"},{"codigo":"RNDND0802","detalle":"TUBERIA DE COBRE 1/2"},{"codigo":"INDND0754","detalle":"TUBERIA DE COBRE 1/4"},{"codigo":"INDND0357","detalle":"TUBERIA DE COBRE 3/8"},{"codigo":"RNDND0440","detalle":"TUBO DE COBRE 1/8 X 15M"},{"codigo":"INDND0169","detalle":"TUERCA HEXAGONAL 1/4 ZINCADO"},{"codigo":"INDND1536","detalle":"UNION SOLDABLE 1/4"}],"CARRIER":[{"codigo":"10-00439-01","detalle":"AMPERIMETRO"},{"codigo":"22-50088-01","detalle":"CAPACITOR 15UF"},{"codigo":"22-50088-00","detalle":"CAPACITOR 20UF"},{"codigo":"INDND0431","detalle":"CAPACITOR DE 5UF"},{"codigo":"66U1-7842-13","detalle":"CIRCUIT BREAKER 460VAC 25AMP"},{"codigo":"14-00247-20","detalle":"COIL"},{"codigo":"14-00393-10","detalle":"COIL 2010-2012"},{"codigo":"76-00748-00","detalle":"COIL EVAPORATOR"},{"codigo":"14-00393-10","detalle":"COIL EVV"},{"codigo":"14-00247-20","detalle":"COIL VALVE EXPANSION 2008-2010"},{"codigo":"14-00230-24SV","detalle":"COIL SOLENOID"},{"codigo":"14-01091-02","detalle":"COIL SOLENOID 24V"},{"codigo":"18-10134-23","detalle":"COMPRESSOR SCROLL AZUL"},{"codigo":"18-10178-20","detalle":"COMPRESSOR SCROLL PLOMO"},{"codigo":"18-10129-20SV","detalle":"COMPRESSOR CONT 41CFM"},{"codigo":"69NT43-202-20","detalle":"CONDENSOR COIL"},{"codigo":"100043106","detalle":"CONTACTOR 12AMP 10-00431-06"},{"codigo":"RNDND0064","detalle":"CONTACTOR 30 AMP 10-00431-07"},{"codigo":"120057900","detalle":"MICROLINK 3"},{"codigo":"1256002","detalle":"MICROLINK 2I"},{"codigo":"400052000","detalle":"COUPLING M13 LOW"},{"codigo":"400052001","detalle":"COUPLING M15 HIGH"},{"codigo":"69NT20-2083","detalle":"COVER JUNCTION BOX"},{"codigo":"12-00433-03RP","detalle":"DISPLAY"},{"codigo":"14-00393-00SV","detalle":"EEV 2010-2012"},{"codigo":"38-00585-00","detalle":"FAN CONDENSER"},{"codigo":"38-00599-00","detalle":"FAN EVAPORATOR"},{"codigo":"INDND1585","detalle":"THERMISTOR TEMP SENSOR"},{"codigo":"3504979","detalle":"HEATER BAR 750W"},{"codigo":"296660300","detalle":"HEATER 750V 230V"},{"codigo":"14-00221-04","detalle":"INDICATOR SIGHTGLASS R134A"},{"codigo":"79-66669-02","detalle":"KEYPAD ASSY"},{"codigo":"INDND2389","detalle":"KIT EMPAQUETADURA CARRIER"},{"codigo":"12-00495-02SV","detalle":"KIT AMBIENT/DEFROST SENSOR"},{"codigo":"12-00425-00","detalle":"MODULE CONTROLLER MICRO-LINK 2i"},{"codigo":"INDND0904","detalle":"MOTOR EVAPORADOR TRIFASICO"},{"codigo":"54-00586-20","detalle":"MOTOR CONDENSER"},{"codigo":"54-00585-20","detalle":"MOTOR EVAPORADOR MONOFASICO"},{"codigo":"30-00407-02SV","detalle":"PACK BATTERY DATACORDER"},{"codigo":"10-00388-00","detalle":"POWERPACK STEPPER MOTOR"},{"codigo":"12-00500-01SV","detalle":"SENSOR COMBINATION RETURN"},{"codigo":"12-00745-00SV","detalle":"SENSOR HUMIDITY W/BRACKET"},{"codigo":"12-00395-01SV","detalle":"SENSOR THERMISTOR SUPPLY"},{"codigo":"12-00309-06","detalle":"SWITCH HIGH PRESSURE HPS"},{"codigo":"RNDND0131","detalle":"SWITCH THERMOSTAT"},{"codigo":"65-00185-03","detalle":"TANQUE RECIBIDOR"},{"codigo":"17-40075-05","detalle":"TERMINAL PLATE"},{"codigo":"12-00352-00","detalle":"TRANSDUCER PRESSURE HIGH"},{"codigo":"12-00352-07SV","detalle":"TRANSDUCER PRESSURE LOW"},{"codigo":"INDND2612","detalle":"TRANSFORMER ELECTRIC CONTROL 440/24V"},{"codigo":"12-00655-01","detalle":"TRANSDUCER PRIME LINE"},{"codigo":"14-00247-01","detalle":"VALVE"},{"codigo":"140027308","detalle":"VALVE HERMETIC TXV THINLINE"},{"codigo":"14-00204-04","detalle":"VALVE DISCHARGE DPRV"},{"codigo":"14-00247-01","detalle":"VALVE EVAPORATOR EXPANSION"},{"codigo":"14-00232-33","detalle":"VALVE EXPANSION"},{"codigo":"14-00206-00","detalle":"VALVE SERVICE"},{"codigo":"14-00206-01","detalle":"VALVE SERVICE"},{"codigo":"14-00353-04","detalle":"VALVE STEPPER MOTOR"},{"codigo":"810147200","detalle":"TUBE ASSY DISCHARGE"},{"codigo":"14-00232-03","detalle":"VALVE TXV"},{"codigo":"INDND2543","detalle":"BOMBA DE VACIO CPS VP6D"},{"codigo":"RNDND0293","detalle":"CHISPEROS"},{"codigo":"INDND0411","detalle":"ACEITE POLYOLESTER"},{"codigo":"INDND0078","detalle":"AFLOJATODO"},{"codigo":"IND0672","detalle":"ARANDELA DE PRESION ZINC 1/4"},{"codigo":"INDND0175","detalle":"BROCA DE COBALTO HSS 1/4"},{"codigo":"INDND3242","detalle":"BROCA DE COBALTO HSS 3/16"},{"codigo":"INDND0134","detalle":"BROCA DE COBALTO HSS 3/8"},{"codigo":"INDND1448","detalle":"CABLE FLEXIBLE AUTOMOTRIZ GTP 10AWG"},{"codigo":"RNDND0558","detalle":"CABLE TW-80 N° 14 AWG"},{"codigo":"INDND0199","detalle":"CABLE VULCANIZADO 3X16"},{"codigo":"INDND0259","detalle":"CABLE VULCANIZADO 4X10"},{"codigo":"INDND1589","detalle":"CAÑA DE SOLDAR"},{"codigo":"RNDND0254","detalle":"CINTA AISLANTE 3M"},{"codigo":"INDND0432","detalle":"CINTA FOAM 1/8 X 2 X 9.4 M"},{"codigo":"INDND0434","detalle":"CINTILLO DE AMARRE 250MM"},{"codigo":"INDND1008","detalle":"CINTILLO DE AMARRE 350MM"},{"codigo":"INDND1911","detalle":"EMPAQUE DE ASBESTO"},{"codigo":"RNDND0198","detalle":"ENCHUFE 32AMP 3P+T 440V ROJO 3H IP67"},{"codigo":"INDND5002","detalle":"EXTENSION CORRIENTE 3X16 30MTS"},{"codigo":"818738A","detalle":"FILTER DRYER, R134A AND R513A (12 PCS)"},{"codigo":"INDND0194","detalle":"FUSIBLE TIPO UÑA 10 AMP"},{"codigo":"INDND0193","detalle":"FUSIBLE TIPO UÑA 5 AMP"},{"codigo":"INDND0648","detalle":"FUSIBLE TIPO UÑA 7.5 AMP"},{"codigo":"INDND2144","detalle":"GAS MAP PRO"},{"codigo":"INDND0120","detalle":"GAS REFRIGERANTE R-134A X 13.60KG"},{"codigo":"RNDND0423","detalle":"GRASA GRA LGMT 3/1"},{"codigo":"RNDND0438","detalle":"JABON LIQUIDO"},{"codigo":"INDND0054","detalle":"LIJA FIERRO #120"},{"codigo":"INDND0079","detalle":"LIMPIA CONTACTO"},{"codigo":"INDND2300","detalle":"MANGA TERMOCONTRAIBLE 25MM"},{"codigo":"INDND3075","detalle":"MANGA TERMOCONTRAIBLE 3MM"},{"codigo":"INDND2974","detalle":"MANGA TERMOCONTRAIBLE 5MM"},{"codigo":"INDND1417","detalle":"PERNO HEX ZINC 1/4 X 1"},{"codigo":"INDND0108","detalle":"PINTURA EN SPRAY ALUMINIO"},{"codigo":"INDND0107","detalle":"PINTURA EN SPRAY NEGRO"},{"codigo":"INDND1547","detalle":"PORTA FUSIBLE AEREO"},{"codigo":"RNDND0100","detalle":"RELAY 24V 720W"},{"codigo":"INDND0171","detalle":"REMACHE POP DE ALUMINIO 3/16X1"},{"codigo":"INDND0173","detalle":"REMACHE POP DE ALUMINIO 3/16X1/2"},{"codigo":"RNDND0260","detalle":"RODAMIENTO 6203"},{"codigo":"INDND0081","detalle":"RODAMIENTO 6205"},{"codigo":"INDND0121","detalle":"SOLDADURA DE PLATA 0%"},{"codigo":"INDND2553","detalle":"SOLDADURA DE PLATA 15%"},{"codigo":"INDND2901","detalle":"SOLDADURA DE PLATA 5%"},{"codigo":"INDND0087","detalle":"SOLVENTE DIELECTRICO SDL-25"},{"codigo":"INDND1962","detalle":"TERMINAL AISLADO TIPO BALA HEMBRA AZUL"},{"codigo":"INDND1097","detalle":"TERMINAL AISLADO TIPO BALA MACHO AZUL"},{"codigo":"INDND0017","detalle":"TRAPO INDUSTRIAL SUELTO"},{"codigo":"RNDND0802","detalle":"TUBERIA DE COBRE 1/2"},{"codigo":"INDND0754","detalle":"TUBERIA DE COBRE 1/4"},{"codigo":"INDND0169","detalle":"TUERCA HEXAGONAL 1/4 ZINCADO"}],"DAIKIN":[{"codigo":"1612576","detalle":"ACCESS PANEL EVAPORADOR"},{"codigo":"1387173","detalle":"AIR COOLED CONDENSER"},{"codigo":"1588349","detalle":"AIR DISCHARGE GRILLE"},{"codigo":"0954633","detalle":"BOARD PTCT BOBINA DE CORRIENTE"},{"codigo":"1270408","detalle":"BODY SMV"},{"codigo":"1270390","detalle":"COIL SMV"},{"codigo":"1266290","detalle":"COIL SOLENOID VALVE"},{"codigo":"1315426","detalle":"COMPRESOR"},{"codigo":"0954936","detalle":"CONDENSER FAN"},{"codigo":"1787494","detalle":"CONTROL BOX COMPLETO"},{"codigo":"11739318","detalle":"CONTROL BOX COVER WELDING"},{"codigo":"1295553","detalle":"CONTROL PANEL"},{"codigo":"1010815","detalle":"DISPLAY"},{"codigo":"1241385","detalle":"DRIER ASSY"},{"codigo":"1381120","detalle":"EARTH LEAKAGE CIRCUIT BREAKER"},{"codigo":"1254538","detalle":"ELECTRONIC EXPANSION VALVE BODY ASSY"},{"codigo":"138143J","detalle":"ELECTRONIC EXPANSION VALVE COIL"},{"codigo":"1787470","detalle":"EVAPORATOR ASSY"},{"codigo":"0777519","detalle":"FAN EVAPORADOR"},{"codigo":"0980618","detalle":"FANBLADE OUTSIDE"},{"codigo":"INDND2552","detalle":"FILTRO SECADOR QDM-164 1/2 QUALITY"},{"codigo":"INDND0024","detalle":"FILTRO SEC. FIJO DE 1/2 FLARE EK 164 STD"},{"codigo":"1787456","detalle":"FRONT PLATE"},{"codigo":"003065J","detalle":"FUSE CONTROLLER"},{"codigo":"1241378","detalle":"HIGH PRESSURE SWITCH"},{"codigo":"1587959","detalle":"HIGH PRESSURE TRANSDUCER HPT"},{"codigo":"1679A30","detalle":"KIT BATTERY"},{"codigo":"1561796","detalle":"LOW FREQUENCY TRANSFORMER"},{"codigo":"1587942","detalle":"LOW PRESSURE TRANSDUCER LPT"},{"codigo":"119891J","detalle":"MAGNETIC CONTACTOR COMPRESSOR"},{"codigo":"119893J","detalle":"MAGNETIC CONTACTOR FANS"},{"codigo":"124149J","detalle":"MAGNETIC CONTACTOR PHASE CORRECTION"},{"codigo":"0955333","detalle":"MOTOR EVAPORADOR"},{"codigo":"2089294","detalle":"NEW COIL VALVE EVV"},{"codigo":"2075473","detalle":"NEW VALVE EXP EVV"},{"codigo":"098333J","detalle":"SENSOR COMP SUCTION TEMP"},{"codigo":"156282J","detalle":"SENSOR EIS"},{"codigo":"156283J","detalle":"SENSOR EOS"},{"codigo":"0798321","detalle":"SENSOR AMBIENT AIR TEMP"},{"codigo":"098332J","detalle":"SENSOR DISCHARGE PIPE TEMP"},{"codigo":"1787247","detalle":"SOLENOID VALVE BODY"},{"codigo":"1256116","detalle":"TERMINAL STRIP VER. 1"},{"codigo":"1679137","detalle":"TERMINAL STRIP VER. 2"},{"codigo":"2346269","detalle":"CONTROL BOX COVER WELDING ASSY"},{"codigo":"1780309","detalle":"BUSHING"},{"codigo":"2272856","detalle":"HEXAGON HEAD BOLT"},{"codigo":"1938968","detalle":"ROLLE"},{"codigo":"1136539","detalle":"CLAMP"},{"codigo":"112894J","detalle":"PACKING"},{"codigo":"1938944","detalle":"CONTROL PANEL WITH SHEET KEY"},{"codigo":"0907062","detalle":"SEAL WASHER"},{"codigo":"2272863","detalle":"PAN HEAD MACHINE SCREW"},{"codigo":"INDND0078","detalle":"ACEITE AFLOJATODO"},{"codigo":"IND411","detalle":"ACEITE POE 68"},{"codigo":"INDND0405","detalle":"ADHESIVO POLIURETANO 540 GRIS 600ML"},{"codigo":"INDND4837","detalle":"SILICON SEALANT 590ML COLOR BLANCO"},{"codigo":"INDND4836","detalle":"SILICON SEALANT 590ML COLOR GREY"},{"codigo":"INDND2543","detalle":"BOMBA DE VACIO CPS VP6D 1/2HP"},{"codigo":"INDND0175","detalle":"BROCA 1/4"},{"codigo":"INDND0176","detalle":"BROCA 3/16"},{"codigo":"INDND0259","detalle":"CABLE VULCANIZADO 4 X10"},{"codigo":"RNDND0254","detalle":"CINTA AISLANTE 3M"},{"codigo":"INDND0432","detalle":"CINTA FOAM 1/8 X 2 X 9.4 M"},{"codigo":"INDND1008","detalle":"CINTILLO DE AMARRE 350MM"},{"codigo":"RNDND0198","detalle":"ENCHUFE 32AMP 3P+T 440V ROJO 3H IP67"},{"codigo":"30650","detalle":"FUSIBLE 10A"},{"codigo":"INDND0120","detalle":"GAS REFRIGERANTE R-134A DE 13.600KG"},{"codigo":"INDN0120","detalle":"GAS R134A"},{"codigo":"RNDND0438","detalle":"JABON LIQUIDO"},{"codigo":"INDND0054","detalle":"LIJA FIERRO #120"},{"codigo":"INDND0079","detalle":"LIMPIA CONTACTO"},{"codigo":"INDND3136","detalle":"PERNO HEX. INOX. M6 X 24"},{"codigo":"INDND0107","detalle":"PINTURA EN SPRAY NEGRO"},{"codigo":"INDND0170","detalle":"REMACHE POP DE ALUMINIO 1/4X1"},{"codigo":"INDND0171","detalle":"REMACHE POP DE ALUMINIO 3/16X1"},{"codigo":"INDND0260","detalle":"RODAMIENTO 6203"},{"codigo":"INDND0081","detalle":"RODAMIENTO 6205"},{"codigo":"INDND0121","detalle":"SOLDADURA DE PLATA 0%"},{"codigo":"INDND2553","detalle":"SOLDADURA DE PLATA 15%"},{"codigo":"INDND2901","detalle":"SOLDADURA DE PLATA 5%"},{"codigo":"INDND0265","detalle":"TERMINAL OJO 5.5-5 / 12-10"},{"codigo":"INDND0882","detalle":"TERMINAL OJO 5.5-8 / 12-10"},{"codigo":"INDND0017","detalle":"TRAPO INDUSTRIAL"},{"codigo":"INDND3344","detalle":"TUBERIA CONDUIT FLEXIBLE C/F PVC 3/8"}]};
  function byId(id){return document.getElementById(id);}
  function clean(s){return String(s==null?'':s).replace(/\s+/g,' ').trim();}
  function norm(s){return clean(s).toUpperCase().normalize('NFD').replace(/[̀-ͯ]/g,'').replace(/[\s_\-]+/g,' ');}
  function esc(s){return clean(s).replace(/[&<>"']/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c];});}
  function toastSafe(t){try{ if(typeof toast==='function') toast(t); else console.log(t); }catch(e){console.log(t);}}
  function controladorKey(){
    const marca = norm(byId('marcaEquipo') && byId('marcaEquipo').value || '');
    const ctrl = norm(byId('controladorEquipo') && byId('controladorEquipo').value || '');
    const joined = marca + ' ' + ctrl;
    if((window.zgGetEquipmentType&&window.zgGetEquipmentType()==='Genset') || joined.includes('SG-3000') || joined.includes('SG 3000') || joined.includes('SG-5000') || joined.includes('SG 5000')){
      if(joined.includes('SG-3000') || joined.includes('SG 3000')) return 'GENSET SG-3000';
      if(joined.includes('SG-5000') || joined.includes('SG 5000')) return 'GENSET SG-5000';
    }
    if(joined.includes('STAR COOL') && (joined.includes('CIM 6') || joined.includes('CIM6'))) return 'STAR COOL CIM 6';
    if(joined.includes('STAR COOL') && (joined.includes('CIM 5') || joined.includes('CIM5'))) return 'STAR COOL CIM 5';
    if(joined.includes('MP5000') || joined.includes('MP 5000')) return 'TK MP5000';
    if(joined.includes('MP4000') || joined.includes('MP 4000')) return 'TK MP4000';
    if(joined.includes('CARRIER')) return 'CARRIER';
    if(joined.includes('DAIKIN')) return 'DAIKIN';
    return '';
  }
  function limpiarPanelItem(r){
    let codigo = clean(r && r.codigo || '');
    let detalle = clean(r && r.detalle || '');
    if(detalle.includes('|')){
      const parts = detalle.split('|').map(clean).filter(Boolean);
      // Si alguien pegó varias cosas en un solo material del panel, mostramos solo el primer nombre limpio.
      if(parts.length) detalle = parts[0].replace(/^[-\d]+\s*/, '').trim();
      for(const p of parts){ const m = p.match(/([A-Z]{2,}\d{3,}|\d{5,})/i); if(!codigo && m){codigo=m[1]; break;} }
    }
    return {codigo, detalle, fuente:'panel'};
  }
  function panelCatalogoLimpio(){
    try{
      return (Array.isArray(REPUESTOS_CATALOGO)?REPUESTOS_CATALOGO:[])
        .map(limpiarPanelItem)
        .filter(x=>x.detalle && !/pendiente de revision/i.test(x.detalle));
    }catch(e){ return []; }
  }
  function catalogoActivo(){
    const key = controladorKey();
    let arr = key ? (ZG_CATALOGOS_POR_CONTROLADOR[key] || []) : [];
    // También se respetan los repuestos creados en panel, pero limpios y sin opción de crear desde técnico.
    // Catálogo del técnico: solo materiales definidos por marca/controlador.
    // Los materiales nuevos o cambios se gestionan desde el panel, no desde esta pantalla.
    const seen = new Set();
    const out = [];
    arr.forEach(x=>{
      const codigo=clean(x.codigo||''); const detalle=clean(x.detalle||'');
      if(!detalle) return;
      const k=(codigo+'|'+detalle).toUpperCase();
      const kd=('DET|'+detalle).toUpperCase();
      if(seen.has(k) || (!codigo && seen.has(kd))) return;
      seen.add(k); seen.add(kd); out.push({codigo, detalle, unidad:'', pendiente_revision:0});
    });
    return out;
  }
  function repuestosFiltradosNuevo(q){
    const query = norm(q||'');
    const arr = catalogoActivo();
    return arr.filter(r=>{
      const c=norm(r.codigo||''), d=norm(r.detalle||'');
      return !query || c.includes(query) || d.includes(query);
    }).sort((a,b)=>{
      const da=norm(a.detalle), db=norm(b.detalle);
      if(query){
        const ax=da.startsWith(query)?0:1, bx=db.startsWith(query)?0:1;
        if(ax!==bx) return ax-bx;
      }
      return String(a.detalle).localeCompare(String(b.detalle),'es',{numeric:true,sensitivity:'base'});
    });
  }
  function renderMenu(items){
    const menu=byId('repuestoSuggest'); if(!menu) return;
    menu.innerHTML='';
    const key = controladorKey();
    if(!key){
      menu.innerHTML='<div class="smart-option" style="cursor:default"><div><span class="smart-main">Selecciona primero la marca y el controlador del equipo.</span><span class="smart-sub">Ejemplo: STAR COOL + CIM 6, THERMO KING + MP5000, CARRIER o DAIKIN.</span></div></div>';
      menu.classList.add('show'); return;
    }
    if(!items.length){
      menu.innerHTML='<div class="smart-option" style="cursor:default"><div><span class="smart-main">No hay coincidencias para este controlador.</span><span class="smart-sub">El técnico no puede crear materiales. Agrégalo desde el panel si corresponde.</span></div></div>';
      menu.classList.add('show'); return;
    }
    items.slice(0,80).forEach(r=>{
      const b=document.createElement('button'); b.type='button'; b.className='smart-option';
      b.innerHTML='<div><span class="smart-main">'+esc(r.detalle)+'</span><span class="smart-sub">'+esc(r.codigo ? 'Código: '+r.codigo : 'Registrado en panel')+'</span></div><span class="smart-badge">usar</span>';
      function usar(ev){
        ev.preventDefault(); ev.stopPropagation(); if(ev.stopImmediatePropagation) ev.stopImmediatePropagation();
        try{
          if(typeof agregarRepuestoObjeto==='function') agregarRepuestoObjeto({codigo:r.codigo||'',detalle:r.detalle||'',unidad:''}, '1', true);
          else if(window.zgRepuestosTablaFinal && typeof window.zgRepuestosTablaFinal.agregar==='function') window.zgRepuestosTablaFinal.agregar({codigo:r.codigo||'',detalle:r.detalle||'',cantidad:'1'}, true);
        }catch(e){console.warn(e);}
        const input=byId('repuestoSearch'); if(input) input.value='';
        menu.classList.remove('show');
        return false;
      }
      b.addEventListener('click', usar, true);
      menu.appendChild(b);
    });
    menu.classList.add('show');
  }
  function mostrarRepuestosNuevo(){
    const input=byId('repuestoSearch');
    const q=input ? input.value : '';
    const items=repuestosFiltradosNuevo(q);
    renderMenu(items);
    const hint=byId('repuestoHint');
    if(hint){
      const key=controladorKey();
      if(!key) hint.textContent='Selecciona marca y controlador para cargar el catálogo correcto. El técnico solo podrá elegir de la lista.';
      else hint.textContent='Catálogo cargado para '+key+'. Selecciona “usar”; no se permite crear materiales desde el técnico.';
    }
  }
  function bloquearManual(){
    const btn=byId('repuestoAddManual'); if(btn){btn.style.display='none'; btn.disabled=true; btn.setAttribute('aria-hidden','true');}
    const input=byId('repuestoSearch');
    if(input){
      input.placeholder='Buscar material según controlador seleccionado';
      input.removeEventListener('input', window.__zgMatInput||(()=>{}));
      window.__zgMatInput = function(){mostrarRepuestosNuevo();};
      input.addEventListener('input', window.__zgMatInput, true);
      input.addEventListener('focus', mostrarRepuestosNuevo, true);
    }
  }
  try{
    window.repuestosFiltrados = repuestosFiltradosNuevo;
    window.mostrarRepuestos = mostrarRepuestosNuevo;
    window.agregarRepuestoManual = function(){toastSafe('El técnico solo puede seleccionar materiales del catálogo. Agrega nuevos desde el panel.'); mostrarRepuestosNuevo(); return false;};
    window.registrarRepuestosTecnico = async function(){ return true; };
  }catch(e){}
  document.addEventListener('click', function(ev){
    if(ev.target && ev.target.closest && ev.target.closest('#repuestoAddManual')){
      ev.preventDefault(); ev.stopPropagation(); if(ev.stopImmediatePropagation) ev.stopImmediatePropagation();
      toastSafe('Esta opción fue deshabilitada. Los materiales nuevos se agregan desde el panel.'); return false;
    }
    if(ev.target && ev.target.closest && ev.target.closest('#repuestoSearch')){ setTimeout(mostrarRepuestosNuevo, 10); }
  }, true);
  document.addEventListener('keydown', function(ev){
    if(ev.target && ev.target.id==='repuestoSearch' && ev.key==='Enter'){ ev.preventDefault(); mostrarRepuestosNuevo(); return false; }
  }, true);
  ['controladorEquipo','marcaEquipo'].forEach(id=>{
    document.addEventListener('change', function(ev){ if(ev.target && ev.target.id===id) setTimeout(mostrarRepuestosNuevo,80); }, true);
    document.addEventListener('input', function(ev){ if(ev.target && ev.target.id===id) setTimeout(mostrarRepuestosNuevo,120); }, true);
  });
  window.addEventListener('load', function(){ bloquearManual(); setTimeout(bloquearManual,700); setTimeout(bloquearManual,1800); });
  document.addEventListener('DOMContentLoaded', bloquearManual);
  window.ZG_CATALOGOS_POR_CONTROLADOR = ZG_CATALOGOS_POR_CONTROLADOR;
})();

(function(){
  function byId(id){return document.getElementById(id);}
  function clean(s){return String(s==null?'':s).replace(/\s+/g,' ').trim();}
  function norm(s){return clean(s).toUpperCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'');}
  function esc(s){return clean(s).replace(/[&<>"']/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c];});}
  function inferUnidad(detalle, codigo){
    const d = norm((detalle||'')+' '+(codigo||''));
    if(/\bM3\b|M³|10\s*M3/.test(d)) return 'm³';
    if(/\bM2\b|M²/.test(d) || /EMPAQUE|ASBESTO|PLANCHA|LAMINA|PLATE/.test(d) && /\d+(?:[\.,]\d+)?\s*X\s*\d+(?:[\.,]\d+)?\s*M\b/.test(d)) return 'm²';
    if(/\bKG\b|KGS|KILOS?|13[\.,]60KG|4[\.,]54\s*KG/.test(d)) return 'kg';
    if(/\bML\b|590ML|600ML|540\s*GR/.test(d)) return 'ml';
    if(/LITRO|LITROS|\b\d+[\.,]?\d*\s*L\b/.test(d)) return 'L';
    if(/CABLE|TUBERIA|TUBO DE COBRE|MANGUERA|TERMOCONTRAIBLE|RIEL DIN|CINTA FOAM|CINTA VULCANIZANTE|CINTA PARA DUCTO|EXTENSION CORRIENTE|CAÑA DE SOLDAR/.test(d)) return 'm';
    if(/REFRIGERANTE|NITROGENO/.test(d)){
      if(/M3|M³/.test(d)) return 'm³';
      if(/KG/.test(d)) return 'kg';
      return 'und';
    }
    if(/ACEITE|SOLVENTE|LIMPIA CONTACTO|JABON LIQUIDO|THINNER|PINTURA|SPRAY|ADHESIVO|SILICON|FUNDENTE|FORMADOR|GRASA/.test(d)) return 'und';
    return 'und';
  }
  window.zgInferUnidadMaterial = inferUnidad;

  function materiales(){
    try{return (window.zgRepuestosTablaFinal && typeof window.zgRepuestosTablaFinal.materiales==='function') ? window.zgRepuestosTablaFinal.materiales() : [];}catch(e){return [];}
  }
  function linea(m){
    const cantidad = String(m.cantidad||'1').replace(/[^0-9]/g,'').slice(0,4) || '1';
    const unidad = clean(m.unidad || inferUnidad(m.detalle,m.codigo) || 'und');
    return (clean(m.codigo)||'-')+' | '+clean(m.detalle)+' | '+cantidad+' | '+unidad;
  }
  function repararCodigosMateriales(arr){
    arr=Array.isArray(arr)?arr:[];
    let catalogo=[];
    try{ catalogo=catalogoActivo(); }catch(e){ catalogo=[]; }
    const key=function(v){return norm(v||'').replace(/\s+/g,' ').trim();};
    arr.forEach(function(m){
      if(!m) return;
      const detalleKey=key(m.detalle);
      let found=catalogo.find(function(r){return key(r.detalle)===detalleKey;});
      if(!found && detalleKey){
        found=catalogo.find(function(r){
          const rk=key(r.detalle);
          return rk && (detalleKey.includes(rk) || rk.includes(detalleKey));
        });
      }
      if(found){
        m.codigo=clean(found.codigo||m.codigo||'');
        m.detalle=clean(found.detalle||m.detalle||'');
        m.unidad=clean(found.unidad||m.unidad||inferUnidad(m.detalle,m.codigo)||'und');
      }else if(!clean(m.codigo)){
        const candidates=(Array.isArray(REPUESTOS_GENSET_CATALOGO)?REPUESTOS_GENSET_CATALOGO:[]);
        const alt=candidates.find(function(r){return key(r.detalle)===detalleKey;});
        if(alt){m.codigo=clean(alt.codigo||'');m.unidad=clean(alt.unidad||m.unidad||'und');}
      }
    });
    return arr;
  }
  window.zgRepararCodigosMateriales=repararCodigosMateriales;
  function guardarConUnidad(){
    const arr = repararCodigosMateriales(materiales());
    arr.forEach(m=>{m.unidad = clean(m.unidad || inferUnidad(m.detalle,m.codigo) || 'und');});
    const ta=byId('repuestosManual');
    if(ta){ta.value = arr.filter(x=>clean(x.detalle)).map(linea).join('\n'); ta.classList.remove('input-error');}
    try{window.repuestosSeleccionados = arr.map(x=>({codigo:clean(x.codigo), detalle:clean(x.detalle), cantidad:String(x.cantidad||'1').replace(/[^0-9]/g,'')||'1', unidad:clean(x.unidad||inferUnidad(x.detalle,x.codigo)), nuevo:!clean(x.codigo)}));}catch(e){}
  }
  function pintarConUnidad(){
    const arr = repararCodigosMateriales(materiales());
    const box=byId('repuestosSelectedList');
    const empty=byId('repuestosEmpty');
    if(!box) return;
    arr.forEach(m=>{m.unidad = clean(m.unidad || inferUnidad(m.detalle,m.codigo) || 'und');});
    box.classList.add('zg-table-ready');
    box.innerHTML='';
    if(empty) empty.classList.toggle('show', arr.length===0);
    if(!arr.length){guardarConUnidad(); return;}
    const count=document.createElement('div');
    count.className='zg-rep-count2';
    count.textContent=arr.length+' material(es) seleccionado(s)';
    box.appendChild(count);
    const wrap=document.createElement('div'); wrap.className='zg-repuestos-table-wrap';
    const table=document.createElement('table'); table.className='zg-repuestos-table';
    table.innerHTML='<thead><tr><th style="width:135px">Código</th><th>Material / repuesto</th><th style="width:105px">Cantidad</th><th style="width:100px">Unidad</th><th style="width:56px"></th></tr></thead><tbody></tbody>';
    const tbody=table.querySelector('tbody');
    arr.forEach(function(it,idx){
      const tr=document.createElement('tr');
      const cod=clean(it.codigo)||'Sin código';
      const unidad=clean(it.unidad||inferUnidad(it.detalle,it.codigo));
      tr.innerHTML='<td data-label="Código"><div class="zg-rep-code2 '+(!clean(it.codigo)?'empty':'')+'">'+esc(cod)+'</div></td>'+
        '<td data-label="Material"><input class="zg-rep-detail2" type="text" value="'+esc(it.detalle)+'" placeholder="Nombre del material"><div class="zg-rep-tip2">Puedes corregir el nombre antes de generar el PDF.</div></td>'+
        '<td data-label="Cantidad"><input class="zg-rep-qty2" type="text" inputmode="numeric" value="'+esc(it.cantidad||'1')+'"></td>'+
        '<td data-label="Unidad"><span class="zg-rep-unit2">'+esc(unidad)+'</span><span class="zg-rep-unit-help">Asignado según material</span></td>'+
        '<td><button type="button" class="zg-rep-del2" title="Quitar">×</button></td>';
      const det=tr.querySelector('.zg-rep-detail2'); const qty=tr.querySelector('.zg-rep-qty2'); const del=tr.querySelector('.zg-rep-del2');
      det.addEventListener('input',function(){arr[idx].detalle=clean(det.value); arr[idx].unidad=inferUnidad(arr[idx].detalle,arr[idx].codigo); guardarConUnidad(); pintarConUnidad();});
      qty.addEventListener('input',function(){let v=String(qty.value||'').replace(/[^0-9]/g,'').slice(0,4); qty.value=v; arr[idx].cantidad=v; guardarConUnidad();});
      qty.addEventListener('focus',function(){setTimeout(function(){try{qty.select();}catch(e){}},30);});
      qty.addEventListener('blur',function(){if(!String(qty.value||'').trim()){qty.value='1'; arr[idx].cantidad='1'; guardarConUnidad();}});
      del.addEventListener('click',function(){arr.splice(idx,1); guardarConUnidad(); pintarConUnidad();});
      tbody.appendChild(tr);
    });
    wrap.appendChild(table); box.appendChild(wrap); guardarConUnidad();
  }
  function keyControlador(){
    const marca=norm(byId('marcaEquipo')&&byId('marcaEquipo').value||'');
    const ctrl=norm(byId('controladorEquipo')&&byId('controladorEquipo').value||'');
    const joined=marca+' '+ctrl;
    if((window.zgGetEquipmentType&&window.zgGetEquipmentType()==='Genset') || joined.includes('SG-3000') || joined.includes('SG 3000') || joined.includes('SG-5000') || joined.includes('SG 5000')){
      if(joined.includes('SG-3000') || joined.includes('SG 3000')) return 'GENSET SG-3000';
      if(joined.includes('SG-5000') || joined.includes('SG 5000')) return 'GENSET SG-5000';
    }
    if(joined.includes('STAR COOL') && (joined.includes('CIM 6') || joined.includes('CIM6'))) return 'STAR COOL CIM 6';
    if(joined.includes('STAR COOL') && (joined.includes('CIM 5') || joined.includes('CIM5'))) return 'STAR COOL CIM 5';
    if(joined.includes('MP5000') || joined.includes('MP 5000')) return 'TK MP5000';
    if(joined.includes('MP4000') || joined.includes('MP 4000')) return 'TK MP4000';
    if(joined.includes('CARRIER')) return 'CARRIER';
    if(joined.includes('DAIKIN')) return 'DAIKIN';
    return '';
  }
  function panelCatalogo(){
    try{return (Array.isArray(window.REPUESTOS_CATALOGO)?window.REPUESTOS_CATALOGO:[]).map(r=>({codigo:clean(r.codigo||''), detalle:clean(r.detalle||''), unidad:clean(r.unidad||'')})).filter(r=>r.detalle);}catch(e){return [];}
  }
  function catalogoActivo(){
    const k=keyControlador();
    let arr=k && window.ZG_CATALOGOS_POR_CONTROLADOR ? (window.ZG_CATALOGOS_POR_CONTROLADOR[k]||[]) : [];
    // No mezclamos el catálogo general del panel aquí para evitar repuestos duplicados o pegados.
    // Esta pantalla carga solo por marca/controlador seleccionado.
    const seen=new Set(); const out=[];
    arr.forEach(x=>{const codigo=clean(x.codigo||''); const detalle=clean(x.detalle||''); if(!detalle) return; const unidad=clean(x.unidad||inferUnidad(detalle,codigo)); const key=(codigo+'|'+detalle).toUpperCase(); if(seen.has(key)) return; seen.add(key); out.push({codigo,detalle,unidad});});
    return out;
  }
  function renderMenuUnidad(items){
    const menu=byId('repuestoSuggest'); if(!menu) return;
    menu.innerHTML='';
    const k=keyControlador();
    if(!k){menu.innerHTML='<div class="smart-option" style="cursor:default"><div><span class="smart-main">Selecciona primero la marca y el controlador del equipo.</span><span class="smart-sub">Luego aparecerán materiales con su unidad de medida.</span></div></div>'; menu.classList.add('show'); return;}
    if(!items.length){menu.innerHTML='<div class="smart-option" style="cursor:default"><div><span class="smart-main">No hay coincidencias para este controlador.</span><span class="smart-sub">El técnico solo puede seleccionar del catálogo.</span></div></div>'; menu.classList.add('show'); return;}
    items.slice(0,80).forEach(r=>{
      const unidad=clean(r.unidad||inferUnidad(r.detalle,r.codigo));
      const b=document.createElement('button'); b.type='button'; b.className='smart-option';
      b.innerHTML='<div><span class="smart-main">'+esc(r.detalle)+'</span><span class="smart-sub">'+esc(r.codigo?'Código: '+r.codigo:'Registrado en panel')+' <span class="unit-pill">'+esc(unidad)+'</span></span></div><span class="smart-badge">usar</span>';
      function usar(ev){ev.preventDefault();ev.stopPropagation();if(ev.stopImmediatePropagation)ev.stopImmediatePropagation(); try{window.agregarRepuestoObjeto({codigo:r.codigo||'',detalle:r.detalle||'',unidad:unidad},'1',true);}catch(e){} menu.classList.remove('show'); const input=byId('repuestoSearch'); if(input) input.value=''; setTimeout(pintarConUnidad,30); return false;} b.addEventListener('click',usar,true); menu.appendChild(b);
    });
    menu.classList.add('show');
  }
  function mostrarConUnidad(){
    const q=norm(byId('repuestoSearch')&&byId('repuestoSearch').value||'');
    const items=catalogoActivo().filter(r=>!q || norm(r.codigo).includes(q) || norm(r.detalle).includes(q)).sort((a,b)=>String(a.detalle).localeCompare(String(b.detalle),'es',{numeric:true,sensitivity:'base'}));
    renderMenuUnidad(items);
    const hint=byId('repuestoHint'); if(hint) hint.textContent='Catálogo con unidad automática. Selecciona “usar” y ajusta solo la cantidad.';
  }
  function instalar(){
    if(window.ZG_CATALOGOS_POR_CONTROLADOR){Object.keys(window.ZG_CATALOGOS_POR_CONTROLADOR).forEach(k=>{(window.ZG_CATALOGOS_POR_CONTROLADOR[k]||[]).forEach(r=>{r.unidad=clean(r.unidad||inferUnidad(r.detalle,r.codigo));});});}
    const oldAdd=window.agregarRepuestoObjeto;
    if(typeof oldAdd==='function' && !oldAdd.__zgUnidad){
      const nuevo=function(r,cantidad,aviso){
        r=r||{}; r.unidad=clean(r.unidad||inferUnidad(r.detalle,r.codigo));
        const res=oldAdd.call(this,r,cantidad,aviso);
        setTimeout(function(){materiales().forEach(m=>{m.unidad=clean(m.unidad||inferUnidad(m.detalle,m.codigo));}); pintarConUnidad();},30);
        return res;
      };
      nuevo.__zgUnidad=true; window.agregarRepuestoObjeto=nuevo;
    }
    if(window.zgRepuestosTablaFinal){window.zgRepuestosTablaFinal.pintar=pintarConUnidad; window.zgRepuestosTablaFinal.guardar=guardarConUnidad;}
    window.renderRepuestosSeleccionados=pintarConUnidad;
    window.syncRepuestosManual=guardarConUnidad;
    window.mostrarRepuestos=mostrarConUnidad;
    window.repuestosFiltrados=function(q){const nq=norm(q||''); return catalogoActivo().filter(r=>!nq||norm(r.codigo).includes(nq)||norm(r.detalle).includes(nq));};
    const input=byId('repuestoSearch');
    if(input && !input.dataset.zgUnidadOk){input.dataset.zgUnidadOk='1'; input.placeholder='Buscar material según controlador seleccionado'; input.addEventListener('focus',function(){setTimeout(mostrarConUnidad,60);},true); input.addEventListener('input',function(){setTimeout(mostrarConUnidad,60);},true);}
    pintarConUnidad();
  }
  document.addEventListener('click',function(ev){
    if(ev.target && ev.target.closest && (ev.target.closest('#pdfBtn') || ev.target.closest('#preBtn'))){guardarConUnidad();}
    if(ev.target && ev.target.closest && ev.target.closest('#repuestoSearch')) setTimeout(mostrarConUnidad,80);
  },true);
  document.addEventListener('keydown',function(ev){if(ev.target && ev.target.id==='repuestoSearch' && ev.key==='Enter'){ev.preventDefault(); mostrarConUnidad(); return false;}},true);
  window.addEventListener('load',function(){instalar(); setTimeout(instalar,700); setTimeout(instalar,1800);});
  document.addEventListener('DOMContentLoaded',instalar);
})();

(function(){
  function byId(id){return document.getElementById(id);}
  function clean(s){return String(s==null?'':s).replace(/\s+/g,' ').trim();}
  function toastSafe(t){try{ if(typeof toast==='function') toast(t); else console.log(t); }catch(e){console.log(t);}}

  // Quitar la tarjeta de preparación técnica de esta pantalla. Esa parte será otro módulo.
  function quitarSalida(){
    document.querySelectorAll('.salida-supervision-card,#salidaSupervisionCard').forEach(function(x){x.remove();});
  }
  window.renderSalidaSupervision = function(){};
  document.addEventListener('DOMContentLoaded', quitarSalida);
  window.addEventListener('load', function(){quitarSalida(); setTimeout(quitarSalida,600); setTimeout(quitarSalida,1600);});

  // Evidencias preliminares: conservarlas al pasar de modo=cliente al enlace con token.
  function tokenActual(){
    const url = new URL(window.location.href);
    return clean(url.searchParams.get('token') || (byId('tokenContinuacion')&&byId('tokenContinuacion').value) || '');
  }
  function reporteActual(){return clean((byId('orden')&&byId('orden').value) || '');}
  function evKeyToken(t){return 'zg_pre_evidencias_token_'+t;}
  function evKeyReporte(r){return 'zg_pre_evidencias_reporte_'+r;}
  function readStored(){
    const t=tokenActual(), r=reporteActual();
    let raw='';
    if(!t && !r) return [];
    try{
      if(t) raw=localStorage.getItem(evKeyToken(t))||'';
      if(!raw && r) raw=localStorage.getItem(evKeyReporte(r))||'';
      if(!raw) raw=sessionStorage.getItem('zg_pre_evidencias_pending')||'';
      const arr=raw?JSON.parse(raw):[];
      return Array.isArray(arr)?arr:[];
    }catch(e){return [];}
  }
  function saveStored(){
    const arr = Array.isArray(window.ZG_PRE_EVIDENCIAS) ? window.ZG_PRE_EVIDENCIAS : [];
    if(!arr.length) return;
    try{
      const data=JSON.stringify(arr);
      sessionStorage.setItem('zg_pre_evidencias_pending', data);
      const r=reporteActual(); if(r) localStorage.setItem(evKeyReporte(r), data);
      const t=tokenActual(); if(t) localStorage.setItem(evKeyToken(t), data);
    }catch(e){console.warn('No se pudieron guardar evidencias preliminares en el navegador', e);}
  }
  function restoreStored(){
    const arr=readStored();
    if(arr.length){
      window.ZG_PRE_EVIDENCIAS = arr;
      if(typeof window.renderPreEvidenceGrid==='function') window.renderPreEvidenceGrid();
      try{ if(typeof updateCounter==='function') updateCounter(); }catch(e){}
    }
    window.__zgPreEvidenceRestored = true;
  }
  function wrapEvidenceRender(){
    if(typeof window.renderPreEvidenceGrid==='function' && !window.renderPreEvidenceGrid.__zgPersist){
      const old=window.renderPreEvidenceGrid;
      window.renderPreEvidenceGrid=function(){
        const r=old.apply(this,arguments);
        if(window.__zgPreEvidenceRestored) saveStored();
        return r;
      };
      window.renderPreEvidenceGrid.__zgPersist=true;
    }
  }
  function wrapGuardarPreliminar(){
    const fn = window.guardarPreinspeccion || (typeof guardarPreinspeccion==='function' ? guardarPreinspeccion : null);
    if(typeof fn==='function' && !fn.__zgEvPersist){
      const nuevo = async function(){
        saveStored();
        const p1=setTimeout(saveStored,300), p2=setTimeout(saveStored,800), p3=setTimeout(saveStored,1200);
        try{return await fn.apply(this,arguments);} finally {setTimeout(saveStored,450); setTimeout(saveStored,900);}
      };
      nuevo.__zgEvPersist=true;
      window.guardarPreinspeccion = nuevo;
      try{ guardarPreinspeccion = nuevo; }catch(e){}
    }
  }
  document.addEventListener('click',function(ev){
    if(ev.target && ev.target.closest && ev.target.closest('#preBtn')){ saveStored(); setTimeout(saveStored,500); setTimeout(saveStored,1000); }
  },true);
  document.addEventListener('change',function(ev){
    if(ev.target && ev.target.id==='preEvidenceInput'){ setTimeout(saveStored,700); setTimeout(saveStored,1800); }
    if(ev.target && ev.target.id==='orden'){ setTimeout(saveStored,100); }
  },true);

  // Ayuda de redacción para campos técnicos. Solo actúa cuando el técnico la activa y presiona el botón.
  function etiquetaDeCampo(el){
    const id=el.id;
    if(id){ const lab=document.querySelector('label[for="'+CSS.escape(id)+'"]'); if(lab) return clean(lab.textContent); }
    const f=el.closest('.field'); if(f){ const lab=f.querySelector('label'); if(lab) return clean(lab.textContent); }
    return 'Observación técnica';
  }
  function capitalizar(s){return s ? s.charAt(0).toUpperCase()+s.slice(1) : s;}
  function normalizarTextoBase(txt){
    let s=clean(txt)
      .replace(/\bq\b/gi,'que').replace(/\bxq\b/gi,'porque').replace(/\bpq\b/gi,'porque')
      .replace(/\bsta\b/gi,'está').replace(/\beq\b/gi,'equipo')
      .replace(/\btemp\b/gi,'temperatura').replace(/\brefri\b/gi,'refrigerante')
      .replace(/\s*([,.;:])\s*/g,'$1 ');
    s=s.replace(/\s+/g,' ').trim();
    if(s && !/[.!?]$/.test(s)) s+='.';
    return capitalizar(s);
  }
  function zgIaMaterialNormalizado(raw){
    raw=raw||{};
    let codigo=clean(raw.codigo||raw.code||'');
    let detalle=clean(raw.detalle||raw.material||raw.nombre||raw.descripcion||'');
    let cantidad=clean(raw.cantidad||raw.qty||'1')||'1';
    let unidad=clean(raw.unidad||raw.unit||'und')||'und';
    if(/^sin código$/i.test(codigo)) codigo='';
    if(!detalle) return null;
    return {codigo,detalle,cantidad,unidad};
  }
  function zgIaAgregarMaterial(out,seen,raw){
    const m=zgIaMaterialNormalizado(raw);
    if(!m) return;
    const key=(m.codigo+'|'+m.detalle+'|'+m.cantidad+'|'+m.unidad).toUpperCase();
    if(seen.has(key)) return;
    seen.add(key); out.push(m);
  }
  function zgIaMaterialesDelTexto(txt,out,seen){
    String(txt||'').split(/\r?\n/).forEach(function(linea){
      const p=linea.split('|').map(clean);
      if(p.length>=4){
        zgIaAgregarMaterial(out,seen,{codigo:p[0]==='-'?'':p[0],detalle:p.slice(1,-2).join(' | '),cantidad:p[p.length-2],unidad:p[p.length-1]});
      }else if(p.length===3){
        zgIaAgregarMaterial(out,seen,{codigo:p[0]==='-'?'':p[0],detalle:p[1],cantidad:p[2],unidad:'und'});
      }else if(p.length===2){
        zgIaAgregarMaterial(out,seen,{codigo:p[0]==='-'?'':p[0],detalle:p[1],cantidad:'1',unidad:'und'});
      }else if(p.length===1 && p[0]){
        zgIaAgregarMaterial(out,seen,{detalle:p[0],cantidad:'1',unidad:'und'});
      }
    });
  }
  function materialesSeleccionadosParaIa(el){
    const out=[],seen=new Set();
    const panel=el && el.closest ? el.closest('.panel') : null;

    // Fuente principal: estado exacto del trabajo que contiene el campo de texto.
    try{
      const st=panel && panel.__zgWorkState;
      if(st && Array.isArray(st.repuestosTrabajo)) st.repuestosTrabajo.forEach(function(m){zgIaAgregarMaterial(out,seen,m);});
      ['materialesTrabajo','materiales','repuestos'].forEach(function(k){
        if(st && Array.isArray(st[k])) st[k].forEach(function(m){zgIaAgregarMaterial(out,seen,m);});
      });
    }catch(e){}

    // Respaldo visual: tabla de materiales dentro del mismo trabajo.
    if(panel){
      panel.querySelectorAll('.zg-work-material-table tbody tr').forEach(function(tr){
        if(tr.querySelector('.zg-work-material-empty')) return;
        const codigo=clean(tr.querySelector('[data-label="Código"] b,[data-label="Código"],td:nth-child(1)')?.textContent||'');
        const detalle=clean(tr.querySelector('.detail,.zg-rep-detail2,[data-label="Material"] input,td:nth-child(2) input')?.value || tr.querySelector('[data-label="Material"],td:nth-child(2)')?.textContent || '');
        const cantidad=clean(tr.querySelector('.qty,.zg-rep-qty2,[data-label="Cantidad"] input,td:nth-child(3) input')?.value || tr.querySelector('[data-label="Cantidad"],td:nth-child(3)')?.textContent || '1');
        const unidad=clean(tr.querySelector('.unit,[data-label="Unidad"],td:nth-child(4)')?.textContent||'und');
        zgIaAgregarMaterial(out,seen,{codigo,detalle,cantidad,unidad});
      });
    }

    // Respaldo general: tabla global y textarea técnico de materiales.
    document.querySelectorAll('#repuestosSelectedList .zg-repuestos-table tbody tr').forEach(function(tr){
      const codigo=clean(tr.querySelector('.zg-rep-code2,[data-label="Código"],td:nth-child(1)')?.textContent||'');
      const detalle=clean(tr.querySelector('.zg-rep-detail2,[data-label="Material"] input,td:nth-child(2) input')?.value || tr.querySelector('[data-label="Material"],td:nth-child(2)')?.textContent || '');
      const cantidad=clean(tr.querySelector('.zg-rep-qty2,[data-label="Cantidad"] input,td:nth-child(3) input')?.value || tr.querySelector('[data-label="Cantidad"],td:nth-child(3)')?.textContent || '1');
      const unidad=clean(tr.querySelector('[data-label="Unidad"],td:nth-child(4)')?.textContent||'und');
      zgIaAgregarMaterial(out,seen,{codigo,detalle,cantidad,unidad});
    });
    const ta=document.getElementById('repuestosManual');
    if(ta) zgIaMaterialesDelTexto(ta.value,out,seen);
    return out.slice(0,40);
  }
  function datosAnterioresParaIa(el){
    const out=[];
    const add=function(label,value){
      const v=clean(value);
      if(v) out.push(label+': '+v);
    };
    const val=function(id){return clean(document.getElementById(id)?.value||'');};
    add('N° de reporte',val('orden'));
    add('Cliente',val('cliente'));
    add('Tipo de equipo',val('zgTipoEquipo'));
    add('Contenedor o equipo',val('equipoNo'));
    add('Serie',val('serialUnidad'));
    add('Marca',val('marcaEquipo'));
    add('Modelo',val('modeloEquipo'));
    add('Controlador',val('controladorEquipo'));
    add('Año de fabricación',val('anioFabricacion'));
    add('Refrigerante',val('refrigerante'));
    add('Set point',val('setPoint'));
    add('Temperatura ambiente',val('temperaturaAmbiente'));
    add('Retorno de aire',val('retornoAire'));
    add('Suministro de aire',val('suministroAire'));
    add('Presión alta',val('presionAlta'));
    add('Presión baja',val('presionBaja'));
    add('Voltaje L1-L2',val('voltajeL1L2'));
    add('Voltaje L2-L3',val('voltajeL2L3'));
    add('Voltaje L1-L3',val('voltajeL1L3'));
    add('Estado inicial',val('estadoInicial'));
    add('Alarma encontrada',val('alarmaEncontrada'));
    add('Observación inicial',val('observacionInicial'));

    const panel=el && el.closest ? el.closest('.panel') : null;
    const st=panel && panel.__zgWorkState;
    if(st){
      add('Trabajo actual',st.nombre);
      const currentId=String(el && el.id || '');
      Object.entries(st.campos||{}).forEach(function(entry){
        const k=entry[0],v=entry[1];
        if(currentId && currentId===('campo_'+st.id+'_'+k)) return;
        add('Dato previo del trabajo - '+k.replace(/_/g,' '),v);
      });
      const defs=(typeof window.zgGetReeferChecklistItemsForWork==='function')?(window.zgGetReeferChecklistItemsForWork(st)||[]):[];
      const values=(st.reeferChecklist&&typeof st.reeferChecklist==='object')?st.reeferChecklist:{};
      defs.forEach(function(d){
        const key=String(d.key ?? d.n);
        const raw=values[key] ?? values[String(d.n)] ?? '';
        let result='';
        if(d.kind==='three'){
          const v=raw&&typeof raw==='object'?raw:{};
          result=[clean(v.l1)?('L1 '+clean(v.l1)+' '+(d.unit||'')):'',clean(v.l2)?('L2 '+clean(v.l2)+' '+(d.unit||'')):'',clean(v.l3)?('L3 '+clean(v.l3)+' '+(d.unit||'')):''].filter(Boolean).join(', ');
        }else if(d.kind==='okvolt'){
          const v=raw&&typeof raw==='object'?raw:{};
          result=clean(v.modo)==='OK'?'OK':(clean(v.modo)==='V'&&clean(v.valor)?clean(v.valor)+' V':clean(raw&&typeof raw==='object'?raw.valor:raw));
        }else result=clean(raw&&typeof raw==='object'?raw.valor:raw);
        if(result) add('Inspección '+d.n+' - '+d.label,result+(d.unit&&d.kind!=='three'&&d.kind!=='okvolt'&&!result.toLowerCase().includes(String(d.unit).toLowerCase())?' '+d.unit:''));
      });
    }
    return out.slice(0,80);
  }
  function contextoSeleccionado(el){
    const panel=el && el.closest ? el.closest('.panel') : null;
    const titulo=clean(panel?.querySelector('.panel-head .ttl')?.textContent || '');
    const grupos={actividades:[],hallazgos:[],acciones:[]};
    if(panel){
      panel.querySelectorAll('.quick-group').forEach(function(g){
        const nombre=clean(g.querySelector('.quick-title')?.textContent || '').toLowerCase();
        const valores=Array.from(g.querySelectorAll('.qchip.on')).map(function(x){return clean(x.textContent);}).filter(Boolean);
        if(nombre.includes('actividad')) grupos.actividades=valores;
        else if(nombre.includes('hallazgo')) grupos.hallazgos=valores;
        else if(nombre.includes('acción') || nombre.includes('accion')) grupos.acciones=valores;
      });
    }
    let memoriaTrabajo=[];
    try{
      if(st&&typeof window.zgOpcionesTecnicasTrabajo==='function'){
        memoriaTrabajo=[].concat(
          window.zgOpcionesTecnicasTrabajo('actividades',st)||[],
          window.zgOpcionesTecnicasTrabajo('hallazgos',st)||[]
        ).slice(0,40);
      }
    }catch(e){}
    return {titulo,grupos,materiales:materialesSeleccionadosParaIa(el),antecedentes:datosAnterioresParaIa(el),memoriaTrabajo};
  }
  function fraseLista(prefijo, arr){
    if(!Array.isArray(arr) || !arr.length) return '';
    return prefijo+' '+arr.join(', ')+'.';
  }
  function mejorarSegunCampo(label, txt, el){
    const l=label.toLowerCase();
    const base=normalizarTextoBase(txt);
    if(!base) return '';
    const ctx=contextoSeleccionado(el);
    const partes=[];
    const esPreventivo=l.includes('mantenimiento preventivo') || ctx.titulo.toLowerCase().includes('preventivo');
    const esCorrectivo=l.includes('mantenimiento correctivo') || ctx.titulo.toLowerCase().includes('correctivo');

    if(esPreventivo || esCorrectivo){
      const tipoEquipo=clean(document.getElementById('zgTipoEquipo')?.value || '').toLowerCase();
      const equipoTexto=(tipoEquipo.includes('genset') || tipoEquipo.includes('generador')) ? 'del generador' : 'de la máquina reefer';
      partes.push('Durante el '+(esPreventivo?'mantenimiento preventivo':'mantenimiento correctivo')+' '+equipoTexto+' se registró la intervención técnica correspondiente.');
      const a=fraseLista('Las actividades ejecutadas fueron:',ctx.grupos.actividades); if(a) partes.push(a);
      const h=fraseLista('Durante la revisión se identificaron los siguientes hallazgos:',ctx.grupos.hallazgos); if(h) partes.push(h);
      const ac=fraseLista('Las acciones realizadas fueron:',ctx.grupos.acciones); if(ac) partes.push(ac);
      partes.push('Como detalle complementario, '+base.charAt(0).toLowerCase()+base.slice(1));
      return partes.join(' ');
    }
    if(l.includes('problema')) return 'El cliente reportó la siguiente condición: '+base;
    if(l.includes('diagn')) return 'Durante la evaluación técnica se identificó la siguiente condición: '+base;
    if(l.includes('soluci') || l.includes('acción') || l.includes('accion')) return 'Como parte de la intervención se ejecutó la siguiente acción técnica: '+base;
    if(l.includes('observación inicial') || l.includes('observacion inicial')) return 'Antes de iniciar la intervención, el equipo fue encontrado en la siguiente condición: '+base;
    if((l.includes('razón') || l.includes('razon') || l.includes('motivo')) && l.includes('mantenimiento')) return 'Se recomienda programar una intervención adicional debido a la siguiente condición técnica: '+base;
    if(l.includes('observación final') || l.includes('observacion final') || l.includes('funcionamiento')) return 'Al concluir el servicio, se registró la siguiente condición operativa: '+base;
    if(l.includes('recomend')) return 'Como recomendación técnica se indica lo siguiente: '+base;
    if(l.includes('hallazgo')) return 'Durante la inspección se registró el siguiente hallazgo técnico: '+base;
    if(l.includes('detalle técnico') || l.includes('detalle tecnico')) return 'Como detalle técnico de la intervención se registró lo siguiente: '+base;
    return 'Durante la intervención técnica se registró lo siguiente: '+base;
  }
  function instalarAyudaTextos(){
    document.querySelectorAll('textarea').forEach(function(ta){
      if(!ta || ta.dataset.zgRedactorOk) return;
      if(ta.id==='repuestosManual') return;
      if(ta.closest('.pre-ev-item')) return;
      const field=ta.closest('.field');
      if(!field) return;
      ta.dataset.zgRedactorOk='1';
      const wrap=document.createElement('div');
      wrap.className='zg-redactor-help';
      wrap.dataset.zgFor = ta.id || '';
      wrap.innerHTML='<label><input type="checkbox"> Activar ayuda</label><button type="button" disabled>Mejorar redacción</button><small>Organiza tus apuntes en una redacción técnica clara y completa.</small>';
      const chk=wrap.querySelector('input');
      const btn=wrap.querySelector('button');
      chk.addEventListener('change',function(){btn.disabled=!chk.checked;});
      btn.addEventListener('click',async function(){
        if(!chk.checked || btn.dataset.loading==='1') return;
        const original=clean(ta.value);
        const etiqueta=etiquetaDeCampo(ta);
        const ctx=contextoSeleccionado(ta);
        const hayContexto=(ctx.grupos.actividades.length||ctx.grupos.hallazgos.length||ctx.grupos.acciones.length||ctx.materiales.length||ctx.antecedentes.length);
        if(!original && !hayContexto){ ta.focus(); toastSafe('Registra una nota o completa datos técnicos para elaborar el detalle.'); return; }
        const tipoEquipo=clean(document.getElementById('zgTipoEquipo')?.value || '');
        const textoBtn=btn.textContent;
        btn.dataset.loading='1';
        btn.disabled=true;
        btn.textContent='Procesando...';

        try{
          const respuesta=await fetch('mejorar_texto_ia.php',{
            method:'POST',
            headers:{'Content-Type':'application/json','Accept':'application/json'},
            credentials:'same-origin',
            body:JSON.stringify({
              texto:original,
              etiqueta:etiqueta,
              tipo_equipo:tipoEquipo,
              trabajo:ctx.titulo,
              actividades:ctx.grupos.actividades,
              hallazgos:ctx.grupos.hallazgos,
              acciones:ctx.grupos.acciones,
              materiales:ctx.materiales,
              antecedentes:ctx.antecedentes,
              memoria_trabajo:ctx.memoriaTrabajo||[],
              preinspeccion_id:String(document.getElementById('preinspeccionId')?.value||window.PREINSPECCION?.id||window.PREINSPECCION?.pre_id||''),
              token_continuacion:String(document.getElementById('tokenContinuacion')?.value||window.TOKEN_CONTINUACION||'')
            })
          });
          let data={};
          try{ data=await respuesta.json(); }catch(e){}
          if(!respuesta.ok || !data.ok || data.source!=='anthropic' || !clean(data.texto)){
            throw new Error(clean(data.error) || 'La respuesta no provino del asistente de IA.');
          }
          ta.value=clean(data.texto);
          ta.dispatchEvent(new Event('input',{bubbles:true}));
          try{ if(window.zgroupMarcarCambio) window.zgroupMarcarCambio(); }catch(e){}
          toastSafe('Redacción técnica mejorada');
        }catch(error){
          // No se aplica ninguna plantilla local. Si la API falla, se conserva exactamente
          // lo escrito por el técnico para no hacer pasar una regla fija como si fuera IA.
          ta.value=original;
          const mensaje=(error && error.message) ? error.message : 'No se pudo conectar con el asistente de redacción.';
          toastSafe(mensaje);
          console.error('ZGROUP IA:', error);
        }finally{
          btn.dataset.loading='0';
          btn.textContent=textoBtn;
          btn.disabled=!chk.checked;
        }
      });
      ta.insertAdjacentElement('afterend', wrap);
    });
  }
  function limpiarAyudasHuerfanas(){
    document.querySelectorAll('.zg-redactor-help').forEach(function(help){
      const targetId=String(help.dataset.zgFor||'').trim();
      const target=targetId ? document.getElementById(targetId) : null;
      const field=target && target.closest ? target.closest('.field') : null;
      if(!target || !field || help.closest('.field')!==field){
        if(target) delete target.dataset.zgRedactorOk;
        help.remove();
        return;
      }
      const hidden=!!(target.disabled || target.hidden || target.closest('[hidden],.is-hidden'));
      help.hidden=hidden;
    });
  }
  const mo=new MutationObserver(function(){instalarAyudaTextos(); limpiarAyudasHuerfanas(); quitarSalida();});
  function init(){
    wrapEvidenceRender();
    wrapGuardarPreliminar();
    setTimeout(restoreStored,250);
    setTimeout(restoreStored,900);
    instalarAyudaTextos();
    limpiarAyudasHuerfanas();
    quitarSalida();
    try{mo.observe(document.body,{childList:true,subtree:true});}catch(e){}
  }
  if(document.readyState==='loading') document.addEventListener('DOMContentLoaded',init); else init();
  window.addEventListener('load',function(){wrapEvidenceRender(); wrapGuardarPreliminar(); setTimeout(restoreStored,650); setTimeout(function(){instalarAyudaTextos(); limpiarAyudasHuerfanas();},750); quitarSalida();});
})();

(function(){
  function limpiarRecomendacionesUI(){
    document.querySelectorAll('.quick-group').forEach(function(g){
      var t = g.querySelector('.quick-title');
      if(t && String(t.textContent || '').trim().toLowerCase() === 'recomendaciones'){
        g.remove();
      }
    });
  }
  document.addEventListener('DOMContentLoaded', limpiarRecomendacionesUI);
  window.addEventListener('load', function(){ limpiarRecomendacionesUI(); setTimeout(limpiarRecomendacionesUI, 500); });
})();

(function(){
  function byId(id){return document.getElementById(id);}
  function clean(v){return String(v==null?'':v).replace(/\s+/g,' ').trim();}
  function toastSafe(t){try{if(typeof toast==='function')toast(t);else alert(t);}catch(e){alert(t);}}
  const META_RE=/\[\[ZG_META:([A-Za-z0-9+\/=_-]+)\]\]/;
  const META_RE_ALL=/\s*\[\[ZG_META:[A-Za-z0-9+\/=_-]+\]\]\s*/g;
  const BRANDS=['THERMO KING','CARRIER','STAR COOL','DAIKIN','OTRO'];
  const CONTROLLERS={
    'STAR COOL':['CIM5','CIM6'],
    'THERMO KING':['MP3000','MP4000','MP5000'],
    'CARRIER':['MICROLINK 2I','MICROLINK 3','MICROLINK 5'],
    'DAIKIN':['DAIKIN'],
      'OTRO':[]
  };
  let loadedMeta=null;

  function b64Encode(obj){
    try{return btoa(unescape(encodeURIComponent(JSON.stringify(obj))));}catch(e){return '';}
  }
  function b64Decode(str){
    try{return JSON.parse(decodeURIComponent(escape(atob(str))));}catch(e){return null;}
  }
  function compactMeta(meta){
    return {
      m:meta.modalidad||'',
      t:meta.tipoInstalacion||'',
      e:meta.tipoEquipo||'',
      z:meta.tamanoContenedor||'',
      gi:meta.gensetInicial||{},
      r:meta.requiereOtroMantenimiento||'',
      mt:meta.tipoOtroMantenimiento||'',
      o:meta.maquinaObjetivo||'',
      pa:meta.presionAlta||'',
      pb:meta.presionBaja||'',
      q:(meta.maquinas||[]).map(x=>[x.id||'',x.marca||'',x.controlador||'',x.serie||''])
    };
  }
  function expandMeta(raw){
    raw=raw||{};
    return {
      modalidad:raw.modalidad||raw.m||'',
      tipoInstalacion:raw.tipoInstalacion||raw.t||'',
      tipoEquipo:raw.tipoEquipo||raw.e||'',
      tamanoContenedor:raw.tamanoContenedor||raw.z||'',
      gensetInicial:raw.gensetInicial||raw.gi||{},
      requiereOtroMantenimiento:raw.requiereOtroMantenimiento||raw.r||'',
      tipoOtroMantenimiento:raw.tipoOtroMantenimiento||raw.mt||'',
      maquinaObjetivo:raw.maquinaObjetivo||raw.o||'',
      presionAlta:raw.presionAlta||raw.pa||'',
      presionBaja:raw.presionBaja||raw.pb||'',
      maquinas:Array.isArray(raw.maquinas)?raw.maquinas:(Array.isArray(raw.q)?raw.q.map(a=>({id:a[0]||'',marca:a[1]||'',controlador:a[2]||'',serie:a[3]||''})):[])
    };
  }
  function parseMetaText(txt){const m=String(txt||'').match(META_RE); return m?expandMeta(b64Decode(m[1])):null;}
  window.zgStripMetaFromText=function(txt){return String(txt||'').replace(META_RE_ALL,'\n').replace(/\n{3,}/g,'\n\n').trim();};

  function machineRows(){
    const out=[];
    for(let i=1;i<=5;i++){
      out.push({id:'M'+i,marca:clean(byId('zgMachineBrand'+i)?.value),controlador:clean(byId('zgMachineController'+i)?.value),serie:clean(byId('zgMachineSerial'+i)?.value)});
    }
    return out;
  }
  window.zgCollectServiceMeta=function(){
    return {
      modalidad:clean(byId('zgModalidadComercial')?.value),
      tipoInstalacion:clean(byId('zgTipoInstalacion')?.value),
      tipoEquipo:clean(byId('zgTipoEquipo')?.value),
      tamanoContenedor:clean(byId('zgTamanoContenedor')?.value),
      gensetInicial:{horometro:clean(byId('gensetHorometroInicial')?.value),bateria:clean(byId('gensetVoltajeBateriaInicial')?.value),combustible:clean(byId('gensetNivelCombustibleInicial')?.value),aceite:clean(byId('gensetNivelAceiteInicial')?.value),refrigeranteMotor:clean(byId('gensetRefrigeranteMotorInicial')?.value),arranque:clean(byId('gensetArranqueInicial')?.value),frecuencia:clean(byId('gensetFrecuenciaInicial')?.value),presionAceite:clean(byId('gensetPresionAceiteInicial')?.value)},
      requiereOtroMantenimiento:clean(byId('zgRequiereOtroMantenimiento')?.value),
      tipoOtroMantenimiento:clean(byId('zgTipoOtroMantenimiento')?.value),
      maquinaObjetivo:clean(byId('zgMaquinaPreliminarObjetivo')?.value),
      presionAlta:clean(byId('presionAlta')?.value),
      presionBaja:clean(byId('presionBaja')?.value),
      maquinas:machineRows()
    };
  };
  window.zgObservacionInicialConMeta=function(obs){
    const cleanObs=window.zgStripMetaFromText(obs||'');
    const encoded=b64Encode(compactMeta(window.zgCollectServiceMeta()));
    return cleanObs+(encoded?'\n[[ZG_META:'+encoded+']]':'');
  };

  function fillControllerList(i,brand,keep){
    const list=byId('zgMachineControllerList'+i), input=byId('zgMachineController'+i);
    if(!list||!input)return;
    list.innerHTML='';
    (CONTROLLERS[brand]||[]).forEach(v=>{const o=document.createElement('option');o.value=v;list.appendChild(o);});
    input.placeholder=(CONTROLLERS[brand]||[]).length?'Selecciona o escribe controlador':'Escribe controlador';
    if(!keep && !(CONTROLLERS[brand]||[]).includes(input.value)) input.value='';
  }
  function renderMachineRows(){
    const grid=byId('zgMachinesGrid'); if(!grid||grid.children.length)return;
    for(let i=1;i<=5;i++){
      const row=document.createElement('div'); row.className='zg-machine-row';
      row.innerHTML='<div class="zg-machine-badge"><small>TÚNEL</small>Máquina '+i+'</div>'+ 
        '<div class="field"><label for="zgMachineBrand'+i+'">Marca</label><select id="zgMachineBrand'+i+'"><option value="">Seleccionar</option>'+BRANDS.map(b=>'<option value="'+b+'">'+b+'</option>').join('')+'</select><div class="field-error" id="zgMachineBrand'+i+'Error"></div></div>'+ 
        '<div class="field"><label for="zgMachineController'+i+'">Controlador</label><input id="zgMachineController'+i+'" list="zgMachineControllerList'+i+'" autocomplete="off" placeholder="Controlador"><datalist id="zgMachineControllerList'+i+'"></datalist><div class="field-error" id="zgMachineController'+i+'Error"></div></div>'+ 
        '<div class="field zg-machine-serial"><label for="zgMachineSerial'+i+'">N° de serie</label><input id="zgMachineSerial'+i+'" autocomplete="off" placeholder="Serie de la máquina"><div class="field-error" id="zgMachineSerial'+i+'Error"></div></div>';
      grid.appendChild(row);
      const brand=byId('zgMachineBrand'+i), ctrl=byId('zgMachineController'+i), serial=byId('zgMachineSerial'+i);
      brand.addEventListener('change',()=>{fillControllerList(i,brand.value,false);syncTargetMachine();refreshPanels();});
      ctrl.addEventListener('input',()=>{syncTargetMachine();refreshPanels();});
      serial.addEventListener('input',()=>{syncTargetMachine();refreshPanels();});
    }
  }
  function refreshPanels(){try{if(typeof renderPanels==='function')renderPanels();}catch(e){}}
  function markError(id,msg){const x=byId(id),e=byId(id+'Error');if(x){x.classList.add('input-error');x.scrollIntoView({behavior:'smooth',block:'center'});setTimeout(()=>{try{x.focus();}catch(_){}},180);}if(e){e.textContent=msg;e.classList.add('show');}toastSafe(msg);return false;}
  function clearError(id){const x=byId(id),e=byId(id+'Error');if(x)x.classList.remove('input-error');if(e){e.textContent='';e.classList.remove('show');}}
  function selectedMachine(){const id=clean(byId('zgMaquinaPreliminarObjetivo')?.value);return machineRows().find(m=>m.id===id)||null;}
  function setMainSynced(on){let hasPre=false;try{hasPre=typeof PREINSPECCION!=='undefined'&&!!PREINSPECCION;}catch(e){}['marcaEquipo','controladorEquipo','serialUnidad'].forEach(id=>{const x=byId(id);if(!x)return;x.classList.toggle('zg-main-machine-synced',on);if(hasPre&&!on)return;if(x.tagName==='SELECT')x.disabled=!!on;else x.readOnly=!!on;});}
  function syncTargetMachine(){
    const tunnel=clean(byId('zgTipoInstalacion')?.value)==='Túnel';
    setMainSynced(tunnel);
    if(!tunnel)return;
    const m=selectedMachine(); if(!m)return;
    const brand=byId('marcaEquipo'),ctrl=byId('controladorEquipo'),ser=byId('serialUnidad');
    if(brand)brand.value=m.marca||''; if(ctrl)ctrl.value=m.controlador||''; if(ser)ser.value=m.serie||'';
    try{if(typeof actualizarOpcionesControlador==='function')actualizarOpcionesControlador(false);}catch(e){}
  }
  function updateVisibility(){
    const req=clean(byId('zgRequiereOtroMantenimiento')?.value),type=clean(byId('zgTipoInstalacion')?.value);
    byId('zgTipoMantenimientoWrap')?.classList.toggle('is-hidden',req!=='Sí');
    byId('zgTunnelConfig')?.classList.toggle('is-hidden',type!=='Túnel');
    if(type!=='Túnel')setMainSynced(false); else syncTargetMachine();
    refreshPanels();
  }
  function applyMeta(meta){
    meta=expandMeta(meta||{}); loadedMeta=meta;
    if(byId('zgModalidadComercial'))byId('zgModalidadComercial').value=meta.modalidad||'';
    if(byId('zgTipoInstalacion'))byId('zgTipoInstalacion').value=meta.tipoInstalacion||'';
    if(byId('zgTipoEquipo'))byId('zgTipoEquipo').value=meta.tipoEquipo||'';
    if(byId('zgTamanoContenedor'))byId('zgTamanoContenedor').value=meta.tamanoContenedor||'';
    const gi=meta.gensetInicial||{};
    [['gensetHorometroInicial','horometro'],['gensetVoltajeBateriaInicial','bateria'],['gensetNivelCombustibleInicial','combustible'],['gensetNivelAceiteInicial','aceite'],['gensetRefrigeranteMotorInicial','refrigeranteMotor'],['gensetArranqueInicial','arranque']].forEach(function(p){if(byId(p[0]))byId(p[0]).value=gi[p[1]]||'';});
    if(byId('zgRequiereOtroMantenimiento'))byId('zgRequiereOtroMantenimiento').value=meta.requiereOtroMantenimiento||'';
    if(byId('zgTipoOtroMantenimiento'))byId('zgTipoOtroMantenimiento').value=meta.tipoOtroMantenimiento||'';
    if(byId('zgMaquinaPreliminarObjetivo'))byId('zgMaquinaPreliminarObjetivo').value=meta.maquinaObjetivo||'';
    // Las presiones preliminares se guardan dentro de la metadata del servicio.
    // Esto permite recuperarlas al continuar con token aunque la tabla antigua
    // de preinspección todavía no tenga columnas propias para presión.
    if(byId('presionAlta') && meta.presionAlta) byId('presionAlta').value=meta.presionAlta;
    if(byId('presionBaja') && meta.presionBaja) byId('presionBaja').value=meta.presionBaja;
    (meta.maquinas||[]).forEach((m,idx)=>{const i=idx+1;if(byId('zgMachineBrand'+i))byId('zgMachineBrand'+i).value=m.marca||'';fillControllerList(i,m.marca||'',true);if(byId('zgMachineController'+i))byId('zgMachineController'+i).value=m.controlador||'';if(byId('zgMachineSerial'+i))byId('zgMachineSerial'+i).value=m.serie||'';});
    updateVisibility();syncTargetMachine();refreshPanels();
  }
  window.zgValidarConfiguracionServicio=function(){
    ['zgModalidadComercial','zgTipoInstalacion','zgTipoEquipo','zgTamanoContenedor','zgMaquinaPreliminarObjetivo'].forEach(clearError);
    if(!clean(byId('zgModalidadComercial')?.value))return markError('zgModalidadComercial','Selecciona si el servicio corresponde a alquiler o venta.');
    const type=clean(byId('zgTipoInstalacion')?.value);if(!type)return markError('zgTipoInstalacion','Selecciona el tipo de instalación.');
    const equipmentType=clean(byId('zgTipoEquipo')?.value);if(!equipmentType)return markError('zgTipoEquipo','Selecciona si se atenderá un reefer, un genset u otro equipo.');
    if(equipmentType!=='Genset' && !clean(byId('zgTamanoContenedor')?.value))return markError('zgTamanoContenedor','Selecciona el tamaño del contenedor o indica que no aplica.');
    if(equipmentType==='Genset'){
      const req=[['gensetHorometroInicial','horómetro inicial'],['gensetVoltajeBateriaInicial','voltaje de batería'],['gensetNivelCombustibleInicial','nivel de combustible'],['gensetNivelAceiteInicial','nivel de aceite'],['gensetRefrigeranteMotorInicial','nivel de refrigerante del motor'],['gensetArranqueInicial','resultado de la prueba de arranque']];
      for(const p of req){if(!clean(byId(p[0])?.value))return markError(p[0],'Completa '+p[1]+' del genset.');}
    }
    if(type==='Túnel' && equipmentType!=='Genset'){
      for(let i=1;i<=5;i++){
        for(const [suffix,label] of [['Brand','la marca'],['Controller','el controlador'],['Serial','el número de serie']]){const id='zgMachine'+suffix+i;if(!clean(byId(id)?.value))return markError(id,'Completa '+label+' de la máquina '+i+'.');}
      }
      if(!clean(byId('zgMaquinaPreliminarObjetivo')?.value))return markError('zgMaquinaPreliminarObjetivo','Selecciona la máquina que se usará como referencia en la inspección preliminar.');
      syncTargetMachine();
    }
    return true;
  };
  window.zgGetConfiguredMachines=function(){return window.zgCollectServiceMeta().maquinas.filter(m=>m.marca||m.controlador||m.serie);};
  window.zgBuildMachineAssignmentField=function(s){
    if(clean(byId('zgTipoInstalacion')?.value)!=='Túnel')return null;
    const f=document.createElement('div');f.className='field full zg-work-machine-field';
    const l=document.createElement('label');l.textContent='Máquina atendida en este trabajo';
    const sel=document.createElement('select');sel.id='zgWorkMachine_'+s.id;
    sel.innerHTML='<option value="">Seleccionar máquina</option>';
    window.zgGetConfiguredMachines().forEach((m,idx)=>{const op=document.createElement('option');op.value=m.id;op.textContent='Máquina '+(idx+1)+' · '+[m.marca,m.controlador,m.serie?('Serie '+m.serie):''].filter(Boolean).join(' · ');sel.appendChild(op);});
    sel.value=s.maquinaAsignada||'';
    const hint=document.createElement('div');hint.className='field-hint';hint.textContent='Esta selección aparecerá dentro del trabajo correspondiente en el informe final.';
    const err=document.createElement('div');err.className='field-error';err.id='zgWorkMachine_'+s.id+'Error';
    sel.addEventListener('change',()=>{s.maquinaAsignada=sel.value;sel.classList.remove('input-error');err.textContent='';err.classList.remove('show');});
    f.append(l,sel,hint,err);return f;
  };
  window.zgValidarAsignacionTrabajos=function(sections){
    if(clean(byId('zgTipoInstalacion')?.value)!=='Túnel')return true;
    for(const s of (sections||[])){
      if(!clean(s.maquinaAsignada)){
        const id='zgWorkMachine_'+s.id,x=byId(id),e=byId(id+'Error');
        if(x){x.classList.add('input-error');x.scrollIntoView({behavior:'smooth',block:'center'});}
        if(e){e.textContent='Selecciona la máquina atendida en este trabajo.';e.classList.add('show');}
        toastSafe('Selecciona la máquina atendida en '+s.nombre+'.');return false;
      }
    }
    return true;
  };

  function clearNewReportEvidence(){
    const url=new URL(location.href),token=clean(url.searchParams.get('token')),pre=clean(byId('preinspeccionId')?.value);
    let hasPre=false;try{hasPre=typeof PREINSPECCION!=='undefined'&&!!PREINSPECCION;}catch(e){}
    if(token||pre||hasPre)return;
    try{sessionStorage.removeItem('zg_pre_evidencias_pending');localStorage.removeItem('zgroup_preinspeccion_token');localStorage.removeItem('zgroup_preinspeccion_id');}catch(e){}
    window.ZG_PRE_EVIDENCIAS=[];
    try{if(typeof window.renderPreEvidenceGrid==='function')window.renderPreEvidenceGrid();if(typeof updateCounter==='function')updateCounter();}catch(e){}
  }
  window.zgClearDraftCache=clearNewReportEvidence;

  function lockNewFieldsIfContinuation(){
    let hasPre=false;try{hasPre=typeof PREINSPECCION!=='undefined'&&!!PREINSPECCION;}catch(e){}
    if(!hasPre)return;
    ['zgModalidadComercial','zgTipoInstalacion','zgMaquinaPreliminarObjetivo'].forEach(id=>{const x=byId(id);if(x)x.disabled=true;});
    for(let i=1;i<=5;i++)['zgMachineBrand','zgMachineController','zgMachineSerial'].forEach(p=>{const x=byId(p+i);if(x){if(x.tagName==='SELECT')x.disabled=true;else x.readOnly=true;}});
  }
  function init(){
    renderMachineRows();
    ['zgModalidadComercial','zgTipoInstalacion','zgRequiereOtroMantenimiento','zgTipoOtroMantenimiento','zgMaquinaPreliminarObjetivo'].forEach(id=>{const x=byId(id);if(x)x.addEventListener('change',()=>{clearError(id);updateVisibility();syncTargetMachine();});});
    let raw='';try{raw=(typeof PREINSPECCION!=='undefined'&&PREINSPECCION)?PREINSPECCION.observacion_inicial||'':'';}catch(e){}
    const meta=parseMetaText(raw);if(meta)applyMeta(meta);else updateVisibility();
    const obs=byId('observacionInicial');if(obs)obs.value=window.zgStripMetaFromText(obs.value);
    clearNewReportEvidence();
    setTimeout(clearNewReportEvidence,700);
    lockNewFieldsIfContinuation();
    setTimeout(()=>{syncTargetMachine();refreshPanels();},300);
  }
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init);else init();
  window.addEventListener('load',()=>{setTimeout(clearNewReportEvidence,100);setTimeout(()=>{syncTargetMachine();refreshPanels();},500);});
  document.addEventListener('change',ev=>{if(ev.target&&ev.target.id==='preEvidenceInput')window.__zgFreshPreEvidence=true;},true);
  document.addEventListener('click',ev=>{if(ev.target&&ev.target.closest&&ev.target.closest('#newReportBtn')){try{sessionStorage.removeItem('zg_pre_evidencias_pending');localStorage.removeItem('zgroup_preinspeccion_token');localStorage.removeItem('zgroup_preinspeccion_id');if(window.zgClearPressureDraftStorage)window.zgClearPressureDraftStorage();}catch(e){}}},true);
})();

(function(){
  // Las presiones ya no se guardan ni se restauran desde sessionStorage/localStorage.
  // Una continuación válida sigue cargando sus valores únicamente desde PREINSPECCION.
  function limpiarCachePresiones(){
    try{
      [window.sessionStorage, window.localStorage].forEach(function(storage){
        const keys=[];
        for(let i=0;i<storage.length;i++){
          const k=storage.key(i);
          if(k && k.indexOf('zg_presiones_pre_')===0) keys.push(k);
        }
        keys.forEach(function(k){ storage.removeItem(k); });
      });
    }catch(e){}
  }
  limpiarCachePresiones();
  window.zgClearPressureDraftStorage = limpiarCachePresiones;
})();

(function(){
  function byId(id){ return document.getElementById(id); }
  function clean(v){ return String(v == null ? '' : v).replace(/\s+/g,' ').trim(); }
  var META_RE=/\[\[ZG_META:[A-Za-z0-9+\/=_-]+\]\]/;
  var META_RE_ALL=/\s*\[\[ZG_META:[A-Za-z0-9+\/=_-]+\]\]\s*/g;
  function stripMeta(v){ return String(v || '').replace(META_RE_ALL,'\n').replace(/\n{3,}/g,'\n\n').trim(); }

  function limpiarMetaVisible(){
    var obs=byId('observacionInicial');
    if(obs && META_RE.test(String(obs.value||''))) obs.value=stripMeta(obs.value);
    try{ if(typeof PREINSPECCION!=='undefined' && PREINSPECCION) PREINSPECCION.observacion_inicial=stripMeta(PREINSPECCION.observacion_inicial||''); }catch(e){}
  }

  function reportIdentity(){
    try{
      var u=new URL(location.href);
      return clean(u.searchParams.get('token') || (byId('tokenContinuacion')&&byId('tokenContinuacion').value) || (byId('preinspeccionId')&&byId('preinspeccionId').value));
    }catch(e){ return ''; }
  }
  function storageKey(){ var id=reportIdentity(); return id ? 'zg_post_maint_'+id : ''; }
  function clearMaintenanceStorage(){
    try{
      [sessionStorage,localStorage].forEach(function(st){
        var keys=[]; for(var i=0;i<st.length;i++){var k=st.key(i);if(k&&k.indexOf('zg_post_maint_')===0)keys.push(k);} keys.forEach(function(k){st.removeItem(k);});
      });
    }catch(e){}
  }
  window.zgClearMaintenanceStorage=clearMaintenanceStorage;

  function saveMaintenance(){
    var key=storageKey(); if(!key) return;
    try{sessionStorage.setItem(key,JSON.stringify({r:clean(byId('zgRequiereOtroMantenimiento')?.value),t:clean(byId('zgTipoOtroMantenimiento')?.value),m:clean(byId('zgMotivoOtroMantenimiento')?.value)}));}catch(e){}
  }
  function restoreMaintenance(){
    var key=storageKey(); if(!key) return;
    try{
      var raw=sessionStorage.getItem(key); if(!raw)return; var d=JSON.parse(raw)||{};
      var r=byId('zgRequiereOtroMantenimiento'),t=byId('zgTipoOtroMantenimiento'),m=byId('zgMotivoOtroMantenimiento');
      if(r&&!r.value&&d.r)r.value=d.r;if(t&&!t.value&&d.t)t.value=d.t;if(m&&!m.value&&d.m)m.value=d.m;
    }catch(e){}
  }

  function clearError(id){var x=byId(id),e=byId(id+'Error');if(x)x.classList.remove('input-error');if(e){e.textContent='';e.classList.remove('show');}}
  function markError(id,msg){
    var x=byId(id),e=byId(id+'Error');if(x){x.classList.add('input-error');x.scrollIntoView({behavior:'smooth',block:'center'});setTimeout(function(){try{x.focus();}catch(_){}},150);}if(e){e.textContent=msg;e.classList.add('show');}
    try{if(typeof toast==='function')toast(msg);else alert(msg);}catch(err){alert(msg);}return false;
  }

  function updateVisibility(){
    var req=clean(byId('zgRequiereOtroMantenimiento')?.value),show=req==='Sí';
    var typeWrap=byId('zgTipoMantenimientoWrap'),reasonWrap=byId('zgMotivoMantenimientoWrap');
    var type=byId('zgTipoOtroMantenimiento'),reason=byId('zgMotivoOtroMantenimiento');
    [typeWrap,reasonWrap].forEach(function(w){if(w){w.classList.toggle('is-hidden',!show);w.hidden=!show;w.style.display=show?'':'none';}});
    if(type){type.disabled=!show;type.required=show;if(!show)type.value='';}
    if(reason){reason.disabled=!show;reason.required=show;if(!show)reason.value='';}
    if(!show){clearError('zgTipoOtroMantenimiento');clearError('zgMotivoOtroMantenimiento');}
    saveMaintenance();
  }
  window.zgActualizarMantenimientoFinal=updateVisibility;

  function validarMantenimientoFinal(){
    ['zgRequiereOtroMantenimiento','zgTipoOtroMantenimiento','zgMotivoOtroMantenimiento'].forEach(clearError);
    var req=clean(byId('zgRequiereOtroMantenimiento')?.value);
    if(!req)return markError('zgRequiereOtroMantenimiento','Indica si el equipo requiere otro mantenimiento.');
    if(req==='Sí'){
      if(!clean(byId('zgTipoOtroMantenimiento')?.value))return markError('zgTipoOtroMantenimiento','Selecciona el tipo de mantenimiento requerido.');
      var motivo=clean(byId('zgMotivoOtroMantenimiento')?.value);
      if(motivo.length<10)return markError('zgMotivoOtroMantenimiento','Explica la razón del mantenimiento con al menos 10 caracteres para que el cliente pueda entenderla.');
    }
    saveMaintenance();return true;
  }
  window.zgValidarMantenimientoFinal=validarMantenimientoFinal;

  function instalarValidacionPreliminar(){
    window.zgValidarConfiguracionServicio=function(){
      ['zgModalidadComercial','zgTipoInstalacion','zgMaquinaPreliminarObjetivo'].forEach(clearError);
      if(!clean(byId('zgModalidadComercial')?.value))return markError('zgModalidadComercial','Selecciona si el servicio corresponde a alquiler o venta.');
      var tipo=clean(byId('zgTipoInstalacion')?.value);if(!tipo)return markError('zgTipoInstalacion','Selecciona el tipo de instalación.');
      if(tipo==='Túnel'){
        for(var i=1;i<=5;i++){
          var campos=[['Brand','la marca'],['Controller','el controlador'],['Serial','el número de serie']];
          for(var j=0;j<campos.length;j++){var id='zgMachine'+campos[j][0]+i;if(!clean(byId(id)?.value))return markError(id,'Completa '+campos[j][1]+' de la máquina '+i+'.');}
        }
        if(!clean(byId('zgMaquinaPreliminarObjetivo')?.value))return markError('zgMaquinaPreliminarObjetivo','Selecciona la máquina que se usará como referencia en la inspección preliminar.');
        try{if(typeof syncTargetMachine==='function')syncTargetMachine();}catch(e){}
      }
      return true;
    };
  }

  function personalizarAyuda(){
    var ta=byId('zgMotivoOtroMantenimiento');if(!ta)return;
    var help=ta.nextElementSibling;
    while(help && !help.classList.contains('zg-redactor-help')) help=help.nextElementSibling;
    if(!help)return;
    var btn=help.querySelector('button'),small=help.querySelector('small');
    if(btn)btn.textContent='Mejorar explicación';
    if(small)small.textContent='Convierte la razón técnica en una explicación clara y profesional para el cliente.';
  }

  function bind(){
    var req=byId('zgRequiereOtroMantenimiento'),type=byId('zgTipoOtroMantenimiento'),reason=byId('zgMotivoOtroMantenimiento');
    if(req&&!req.dataset.zgMaintBound){req.dataset.zgMaintBound='1';req.addEventListener('change',function(){clearError(req.id);updateVisibility();});}
    if(type&&!type.dataset.zgMaintBound){type.dataset.zgMaintBound='1';type.addEventListener('change',function(){clearError(type.id);saveMaintenance();});}
    if(reason&&!reason.dataset.zgMaintBound){reason.dataset.zgMaintBound='1';reason.addEventListener('input',function(){clearError(reason.id);saveMaintenance();});}
    restoreMaintenance();updateVisibility();personalizarAyuda();
  }

  function init(){limpiarMetaVisible();instalarValidacionPreliminar();bind();[80,250,650,1300,2400].forEach(function(ms){setTimeout(function(){limpiarMetaVisible();instalarValidacionPreliminar();bind();personalizarAyuda();},ms);});}
  document.addEventListener('click',function(ev){
    if(ev.target&&ev.target.closest&&ev.target.closest('#pdfBtn')&&!validarMantenimientoFinal()){ev.preventDefault();ev.stopPropagation();if(ev.stopImmediatePropagation)ev.stopImmediatePropagation();return false;}
    if(ev.target&&ev.target.closest&&(ev.target.closest('#newReportBtn')||ev.target.closest('#clearBtn'))){setTimeout(clearMaintenanceStorage,0);}
  },true);
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init);else init();
  window.addEventListener('load',function(){setTimeout(init,100);});
})();

(function(){
  function byId(id){ return document.getElementById(id); }
  function clone(v){ try{return JSON.parse(JSON.stringify(v));}catch(e){return v;} }
  function fire(el){ if(!el)return; try{el.dispatchEvent(new Event('input',{bubbles:true}));el.dispatchEvent(new Event('change',{bubbles:true}));}catch(e){} }

  window.zgCollectReportSnapshot = function(){
    const fields = {};
    document.querySelectorAll('input[id],select[id],textarea[id]').forEach(function(el){
      if(el.type === 'file') return;
      fields[el.id] = {type:el.type || el.tagName.toLowerCase(), value:el.value == null ? '' : String(el.value), checked:!!el.checked};
    });
    return {
      version: 3,
      fields: fields,
      state: {
        tecnicoId: String(state.tecnicoId || ''),
        tecnicoNombre: String(state.tecnicoNombre || ''),
        customSeq: Number(state.customSeq || 0),
        selected: clone(state.selected || {})
      },
      preEvidence: clone(window.ZG_PRE_EVIDENCIAS || []),
      savedAt: new Date().toISOString()
    };
  };

  function setField(id, value, checked){
    const el=byId(id); if(!el) return;
    if((el.type==='checkbox'||el.type==='radio') && checked !== undefined) el.checked=!!checked;
    if(value !== undefined && value !== null) el.value=String(value);
    fire(el);
  }

  function drawSignature(canvasId, hiddenId, dataUrl){
    if(!dataUrl) return;
    const canvas=byId(canvasId), hidden=byId(hiddenId); if(hidden) hidden.value=dataUrl;
    if(!canvas) return;
    const img=new Image();
    img.onload=function(){
      const rect=canvas.getBoundingClientRect();
      const dpr=Math.max(1,window.devicePixelRatio||1);
      canvas.width=Math.max(500,Math.round(rect.width*dpr));
      canvas.height=Math.max(220,Math.round(rect.height*dpr));
      const ctx=canvas.getContext('2d');
      ctx.fillStyle='#fff';ctx.fillRect(0,0,canvas.width,canvas.height);
      const pad=Math.round(18*dpr), aw=canvas.width-pad*2, ah=canvas.height-pad*2;
      const sc=Math.min(aw/img.width,ah/img.height);
      const w=img.width*sc,h=img.height*sc;
      ctx.drawImage(img,(canvas.width-w)/2,(canvas.height-h)/2,w,h);
      const box=canvas.closest('.firma-box');if(box)box.classList.add('firmado');
    };
    img.src=dataUrl;
  }
  window.zgDrawStoredSignature=drawSignature;

  function restoreSnapshot(snapshot){
    if(!snapshot || typeof snapshot!=='object') return false;
    const fields=snapshot.fields||{};
    Object.keys(fields).forEach(function(id){ const f=fields[id]||{}; setField(id,f.value,f.checked); });
    if(snapshot.state){
      state.tecnicoId=String(snapshot.state.tecnicoId||state.tecnicoId||'');
      state.tecnicoNombre=String(snapshot.state.tecnicoNombre||state.tecnicoNombre||'');
      state.customSeq=Number(snapshot.state.customSeq||0);
      if(snapshot.state.selected && typeof snapshot.state.selected==='object') state.selected=clone(snapshot.state.selected);
    }
    try{renderWorkCards();renderPanels();updateCounter();}catch(e){}
    // Segunda pasada: los paneles dinámicos ya existen.
    Object.keys(fields).forEach(function(id){ const f=fields[id]||{}; setField(id,f.value,f.checked); });
    if(Array.isArray(snapshot.preEvidence)){
      window.ZG_PRE_EVIDENCIAS=clone(snapshot.preEvidence);
      try{renderPreEvidenceGrid();}catch(e){}
    }
    drawSignature('firmaTecnicoCanvas','firmaTecnico',(fields.firmaTecnico||{}).value||'');
    drawSignature('firmaAdminCanvas','firmaAdmin',(fields.firmaAdmin||{}).value||'');
    try{if(window.zgSyncEstadoInicial)window.zgSyncEstadoInicial();}catch(e){}
    try{
      if(typeof window.zgLoadEditMaterials === 'function') window.zgLoadEditMaterials(snapshot);
      else if(window.zgRepuestosTablaFinal && typeof window.zgRepuestosTablaFinal.pintar === 'function') window.zgRepuestosTablaFinal.pintar();
    }catch(e){console.warn('No se pudieron restaurar los materiales del informe',e);}
    return true;
  }
  window.zgRestoreReportSnapshot = restoreSnapshot;

  function restoreLegacy(report){
    if(!report) return;
    const p=report.preinspeccion||{};
    const vals={
      orden:report.orden, cliente:report.cliente, direccion:report.direccion, fecha:report.fecha,
      tecnicoId:report.tecnico_id, tecnicoInput:report.tecnico_nombre, preinspeccionId:report.preinspeccion_id,
      equipoNo:p.numero_equipo, serialUnidad:p.serie_unidad, marcaEquipo:p.marca_equipo, modeloEquipo:p.modelo_equipo,
      controladorEquipo:p.controlador, anioFabricacion:p.anio_fabricacion, refrigerante:p.refrigerante, setPoint:p.set_point,
      temperaturaAmbiente:p.temperatura_ambiente, retornoAire:p.retorno_aire, suministroAire:p.suministro_aire,
      presionAlta:p.presion_alta, presionBaja:p.presion_baja, voltajeL1L2:p.voltaje_l1_l2,
      voltajeL2L3:p.voltaje_l2_l3, voltajeL1L3:p.voltaje_l1_l3, estadoInicial:p.estado_inicial,
      observacionInicial:p.observacion_inicial, direccionCoords:(p.latitud&&p.longitud)?(p.latitud+', '+p.longitud):''
    };
    Object.keys(vals).forEach(function(id){ if(vals[id]!==undefined&&vals[id]!==null)setField(id,vals[id]); });
    state.tecnicoId=String(report.tecnico_id||'');state.tecnicoNombre=String(report.tecnico_nombre||'');
    if(window.zgTecnicoSetById) window.zgTecnicoSetById(state.tecnicoId);
    String(report.trabajos||'').split(/\s*\|\s*|\s*,\s*/).filter(Boolean).forEach(function(n){try{asegurarTrabajoSeleccionado(n);}catch(e){}});
    try{renderWorkCards();renderPanels();updateCounter();}catch(e){}
  }

  function installSupervisorUpload(){
    const canvas=byId('firmaAdminCanvas');if(!canvas)return;
    const box=canvas.closest('.firma-box');if(!box||box.querySelector('.zg-supervisor-upload'))return;
    const title=box.querySelector('.firma-title b');if(title)title.textContent='Firma del supervisor / responsable';
    const wrap=document.createElement('div');wrap.className='zg-supervisor-upload';
    wrap.innerHTML='<b>Subir firma en imagen</b><small>Úsalo cuando el supervisor o responsable no estuvo presente al cerrar el servicio. Acepta JPG o PNG con fondo claro.</small><div class="zg-supervisor-upload-row"><input class="zg-supervisor-file" id="zgSupervisorFirmaFile" type="file" accept="image/png,image/jpeg,image/webp"><button class="zg-supervisor-upload-btn" id="zgSupervisorFirmaBtn" type="button">Usar imagen</button><span class="zg-supervisor-status" id="zgSupervisorFirmaStatus"></span></div>';
    box.appendChild(wrap);
    const input=byId('zgSupervisorFirmaFile'),btn=byId('zgSupervisorFirmaBtn'),status=byId('zgSupervisorFirmaStatus');
    btn.onclick=function(){ if(!input.files||!input.files[0]){alert('Selecciona primero una imagen de la firma.');return;} const file=input.files[0]; if(file.size>6*1024*1024){alert('La imagen supera 6 MB. Usa una foto más ligera.');return;} const fr=new FileReader(); fr.onload=function(){ const img=new Image(); img.onload=function(){ const c=document.createElement('canvas');c.width=1400;c.height=520;const ctx=c.getContext('2d');ctx.fillStyle='#fff';ctx.fillRect(0,0,c.width,c.height);const pad=35,sc=Math.min((c.width-pad*2)/img.width,(c.height-pad*2)/img.height);const w=img.width*sc,h=img.height*sc;ctx.drawImage(img,(c.width-w)/2,(c.height-h)/2,w,h);const data=c.toDataURL('image/jpeg',0.90);const hidden=byId('firmaAdmin');if(hidden)hidden.value=data;drawSignature('firmaAdminCanvas','firmaAdmin',data);status.textContent='Firma cargada';};img.src=String(fr.result||'');};fr.readAsDataURL(file); };
  }

  function abrirDatosGenerales(){
    const card=byId('datosGeneralesCard');
    if(card) card.classList.remove('datos-collapsed');
    const toggle=byId('datosGeneralesToggle');
    if(toggle) toggle.setAttribute('aria-expanded','true');
    const pill=byId('datosGeneralesPill');
    if(pill) pill.textContent='Ocultar datos';
  }
  function desbloquearFormularioCompleto(){
    document.querySelectorAll('main input, main select, main textarea, main button').forEach(function(el){
      if(el.type==='hidden') return;
      if(el.id==='pdfBtn' || el.id==='clearBtn') return;
      el.disabled=false;
      if('readOnly' in el) el.readOnly=false;
      if(el.style) el.style.background='';
    });
    const pick=byId('dirPick');if(pick)pick.style.pointerEvents='';
  }
  function initEditMode(){
    if(!ZG_EDIT_MODE)return;
    document.body.classList.add('zg-editing');
    const banner=byId('zgEditBanner');if(banner)banner.classList.add('show');
    abrirDatosGenerales();
    desbloquearFormularioCompleto();
    const pdfBtn=byId('pdfBtn');if(pdfBtn){pdfBtn.lastChild.textContent=' Actualizar informe y PDF';pdfBtn.title='Guardar todos los cambios y reemplazar el PDF anterior';}
    installSupervisorUpload();
    if(!ZG_EDIT_REPORT||!ZG_EDIT_REPORT.id){alert((ZG_EDIT_REPORT&&ZG_EDIT_REPORT.error)||'No se pudo cargar el informe.');location.href='panel.php';return;}
    const ok=restoreSnapshot(ZG_EDIT_REPORT.snapshot);
    if(!ok)restoreLegacy(ZG_EDIT_REPORT);
    setTimeout(function(){abrirDatosGenerales();desbloquearFormularioCompleto();installSupervisorUpload();if(ZG_EDIT_REPORT.snapshot)restoreSnapshot(ZG_EDIT_REPORT.snapshot);else restoreLegacy(ZG_EDIT_REPORT);desbloquearFormularioCompleto();},800);
    setTimeout(function(){abrirDatosGenerales();desbloquearFormularioCompleto();installSupervisorUpload();},1800);
  }
  function initPreEditMode(){
    if(!ZG_PRE_EDIT_MODE)return;
    document.body.classList.add('zg-pre-editing');
    const banner=byId('zgEditBanner');if(banner)banner.classList.add('show');
    const title=byId('zgEditBannerTitle');if(title)title.textContent='✏️ Edición de inspección preliminar';
    const txt=byId('zgEditBannerText');if(txt)txt.textContent='Corrige primero la inspección preliminar. Al actualizarla se abrirá la continuación del servicio.';
    abrirDatosGenerales();
    desbloquearFormularioCompleto();
    const btn=byId('preBtn');if(btn){btn.disabled=false;btn.textContent='Actualizar preliminar y continuar servicio';btn.style.opacity='1';}
    const st=byId('preStatus');if(st)st.textContent='Edición administrativa habilitada';
    setTimeout(function(){abrirDatosGenerales();desbloquearFormularioCompleto();},500);
  }
  window.addEventListener('load',function(){initEditMode();initPreEditMode();});
})();

(function(){
  function byId(id){ return document.getElementById(id); }
  function clean(s){ return String(s == null ? '' : s).replace(/\s+/g,' ').trim(); }
  function isEdit(){ try{return typeof ZG_EDIT_MODE !== 'undefined' && !!ZG_EDIT_MODE;}catch(e){return document.body.classList.contains('zg-editing');} }
  function inferUnit(detail, code){
    try{ if(typeof window.zgInferUnidadMaterial === 'function') return clean(window.zgInferUnidadMaterial(detail,code)||'und'); }catch(e){}
    return 'und';
  }
  function parseLine(line){
    const p=String(line||'').split('|').map(clean);
    if(!p.length || !p.join('')) return null;
    let code='',detail='',qty='1',unit='und';
    if(p.length>=4){
      code=p[0]==='-'?'':p[0];
      unit=p[p.length-1]||'und';
      qty=(p[p.length-2]||'1').replace(/[^0-9]/g,'')||'1';
      detail=p.slice(1,-2).join(' | ');
    }else if(p.length===3){
      code=p[0]==='-'?'':p[0]; detail=p[1]; qty=(p[2]||'1').replace(/[^0-9]/g,'')||'1'; unit=inferUnit(detail,code);
    }else if(p.length===2){
      code=p[0]==='-'?'':p[0]; detail=p[1]; unit=inferUnit(detail,code);
    }else{ detail=p[0]; unit=inferUnit(detail,''); }
    if(!detail) return null;
    return {codigo:code,detalle:detail,cantidad:qty,unidad:unit};
  }
  function itemsFromTextarea(){
    const ta=byId('repuestosManual');
    return String(ta?ta.value:'').split(/\r?\n/).map(parseLine).filter(Boolean);
  }
  function itemsFromTable(){
    const rows=[...document.querySelectorAll('#repuestosSelectedList .zg-repuestos-table tbody tr')];
    return rows.map(function(row){
      const code=clean(row.querySelector('.zg-rep-code2')?.textContent||'').replace(/^Sin código$/i,'');
      const detail=clean(row.querySelector('.zg-rep-detail2')?.value||'');
      const qty=String(row.querySelector('.zg-rep-qty2')?.value||'1').replace(/[^0-9]/g,'')||'1';
      const unit=clean(row.querySelector('.zg-rep-unit2')?.textContent||inferUnit(detail,code)||'und');
      return detail?{codigo:code,detalle:detail,cantidad:qty,unidad:unit}:null;
    }).filter(Boolean);
  }
  function serialize(items){
    return items.map(function(x){
      return (clean(x.codigo)||'-')+' | '+clean(x.detalle)+' | '+(String(x.cantidad||'1').replace(/[^0-9]/g,'')||'1')+' | '+clean(x.unidad||inferUnit(x.detalle,x.codigo)||'und');
    }).join('\n');
  }
  function selectedYes(){
    const hidden=byId('requiereRepuesto');
    const yes=byId('repuestoSiBtn');
    const no=byId('repuestoNoBtn');
    // La decisión final del técnico manda sobre cualquier material agregado antes.
    // Si “No requiere repuesto” está activo, nunca se reactiva automáticamente por tener filas antiguas.
    if(no && no.classList.contains('on')) return false;
    if(yes && yes.classList.contains('on')) return true;
    return !!(hidden && String(hidden.value||'').toLowerCase()==='si');
  }
  function markRequired(hasMaterials){
    const hidden=byId('requiereRepuesto');
    const card=byId('repuestosCard');
    const yes=byId('repuestoSiBtn');
    const no=byId('repuestoNoBtn');
    // Solo se marca “Sí” si el técnico realmente dejó seleccionada esa opción.
    if(hasMaterials && selectedYes()){
      if(hidden) hidden.value='si';
      if(card) card.classList.remove('is-hidden');
      if(yes) yes.classList.add('on');
      if(no) no.classList.remove('on');
    }
  }
  function updateInternal(items, repaint){
    try{
      if(window.zgRepuestosTablaFinal && typeof window.zgRepuestosTablaFinal.materiales==='function'){
        const arr=window.zgRepuestosTablaFinal.materiales();
        if(Array.isArray(arr)){
          arr.splice(0,arr.length);
          items.forEach(function(x){arr.push({codigo:x.codigo,detalle:x.detalle,cantidad:x.cantidad,unidad:x.unidad});});
          if(repaint && typeof window.zgRepuestosTablaFinal.pintar==='function') window.zgRepuestosTablaFinal.pintar();
          else if(typeof window.zgRepuestosTablaFinal.guardar==='function') window.zgRepuestosTablaFinal.guardar();
        }
      }
      window.repuestosSeleccionados=items.map(function(x){return {codigo:x.codigo,detalle:x.detalle,cantidad:x.cantidad,unidad:x.unidad,nuevo:!x.codigo};});
    }catch(e){console.warn('No se pudo sincronizar la tabla de materiales',e);}
  }
  function clearAllMaterialsForFinalDecision(){
    const hidden=byId('requiereRepuesto');
    const ta=byId('repuestosManual');
    const card=byId('repuestosCard');
    const yes=byId('repuestoSiBtn');
    const no=byId('repuestoNoBtn');
    if(hidden) hidden.value='no';
    if(ta) ta.value='';
    if(card) card.classList.add('is-hidden');
    if(yes) yes.classList.remove('on');
    if(no) no.classList.add('on');

    try{
      if(window.zgRepuestosTablaFinal && typeof window.zgRepuestosTablaFinal.materiales==='function'){
        const arr=window.zgRepuestosTablaFinal.materiales();
        if(Array.isArray(arr)) arr.splice(0,arr.length);
        if(typeof window.zgRepuestosTablaFinal.pintar==='function') window.zgRepuestosTablaFinal.pintar();
        else if(typeof window.zgRepuestosTablaFinal.guardar==='function') window.zgRepuestosTablaFinal.guardar();
      }
    }catch(e){}
    try{ window.repuestosSeleccionados=[]; }catch(e){}
    try{
      Object.values((typeof state!=='undefined' && state.selected) ? state.selected : {}).forEach(function(s){
        if(!s) return;
        ['repuestosTrabajo','materialesTrabajo','materiales','repuestos'].forEach(function(k){
          if(Array.isArray(s[k])) s[k]=[];
          else if(typeof s[k]==='string') s[k]='';
        });
        if(s.campos && typeof s.campos==='object'){
          ['repuestosTrabajo','materialesTrabajo','materiales','repuestos'].forEach(function(k){
            if(Array.isArray(s.campos[k])) s.campos[k]=[];
            else if(typeof s.campos[k]==='string') s.campos[k]='';
          });
        }
      });
    }catch(e){}
    window.ZG_WORK_MATERIALS_PDF={};
    return [];
  }
  function syncBeforeSave(){
    // Si la decisión final es “No”, se descartan todas las selecciones anteriores.
    if(!selectedYes()) return clearAllMaterialsForFinalDecision();

    let items=itemsFromTable();
    if(!items.length) items=itemsFromTextarea();
    const ta=byId('repuestosManual');
    if(items.length){
      if(ta) ta.value=serialize(items);
      markRequired(true);
      updateInternal(items,false);
    }
    return items;
  }
  let materialsRestored=false;
  function sourceFromReport(snapshot){
    let txt='';
    try{ txt=String((snapshot&&snapshot.fields&&snapshot.fields.repuestosManual&&snapshot.fields.repuestosManual.value)||'').trim(); }catch(e){}
    if(!txt){
      try{ txt=String((ZG_EDIT_REPORT&&ZG_EDIT_REPORT.repuestos_manual)||'').trim(); }catch(e){}
    }
    if(!txt){
      try{ txt=String((ZG_EDIT_REPORT&&ZG_EDIT_REPORT.snapshot&&ZG_EDIT_REPORT.snapshot.fields&&ZG_EDIT_REPORT.snapshot.fields.repuestosManual&&ZG_EDIT_REPORT.snapshot.fields.repuestosManual.value)||'').trim(); }catch(e){}
    }
    return txt;
  }
  function restoreMaterials(snapshot){
    if(!isEdit() || materialsRestored) return;
    const ta=byId('repuestosManual');
    const source=sourceFromReport(snapshot);
    if(ta && source) ta.value=source;
    const items=itemsFromTextarea();
    if(!items.length) return;
    markRequired(true);
    updateInternal(items,true);
    if(ta) ta.value=serialize(items);
    const apiReady=!!(window.zgRepuestosTablaFinal && typeof window.zgRepuestosTablaFinal.materiales==='function');
    const rows=document.querySelectorAll('#repuestosSelectedList .zg-repuestos-table tbody tr').length;
    if(apiReady && (rows>0 || items.length>0)) materialsRestored=true;
  }
  window.zgLoadEditMaterials=function(snapshot){ restoreMaterials(snapshot||null); };
  function wrapSnapshot(){
    const old=window.zgCollectReportSnapshot;
    if(typeof old!=='function' || old.__zgMaterialsFix) return;
    const next=function(){
      const requiere=selectedYes();
      const items=requiere ? syncBeforeSave() : clearAllMaterialsForFinalDecision();
      const snap=old.apply(this,arguments)||{};
      snap.fields=snap.fields||{};
      const txt=requiere ? serialize(items) : '';
      snap.fields.repuestosManual={type:'textarea',value:txt,checked:false};
      snap.fields.requiereRepuesto={type:'hidden',value:requiere?'si':'no',checked:false};
      if(!requiere && snap.state && snap.state.selected){
        Object.values(snap.state.selected).forEach(function(s){
          if(!s) return;
          s.repuestosTrabajo=[]; s.materialesTrabajo=[]; s.materiales=[]; s.repuestos=[];
          if(s.campos){
            s.campos.repuestosTrabajo=[]; s.campos.materialesTrabajo=[];
            s.campos.materiales=[]; s.campos.repuestos=[];
          }
        });
      }
      return snap;
    };
    next.__zgMaterialsFix=true;
    window.zgCollectReportSnapshot=next;
  }
  function wrapGenerate(){
    let old=null;
    try{ old=window.generatePDF || generatePDF; }catch(e){ old=window.generatePDF; }
    if(typeof old!=='function' || old.__zgMaterialsFix) return;
    const next=async function(){
      if(selectedYes()) syncBeforeSave();
      else clearAllMaterialsForFinalDecision();
      return await old.apply(this,arguments);
    };
    next.__zgMaterialsFix=true;
    window.generatePDF=next;
    try{ generatePDF=next; }catch(e){}
  }
  function install(){
    wrapSnapshot(); wrapGenerate();
    if(isEdit()) restoreMaterials((typeof ZG_EDIT_REPORT!=='undefined'&&ZG_EDIT_REPORT)?ZG_EDIT_REPORT.snapshot:null);
  }
  document.addEventListener('click',function(ev){
    if(!ev.target || !ev.target.closest) return;
    if(ev.target.closest('#repuestoNoBtn')){
      // Se ejecuta después de los manejadores anteriores para que “No” sea definitivo.
      setTimeout(clearAllMaterialsForFinalDecision,0);
      setTimeout(clearAllMaterialsForFinalDecision,80);
      return;
    }
    if(ev.target.closest('#pdfBtn')){
      if(selectedYes()) syncBeforeSave();
      else clearAllMaterialsForFinalDecision();
    }
  },true);
  document.addEventListener('input',function(ev){
    if(ev.target && ev.target.closest && ev.target.closest('#repuestosSelectedList')) setTimeout(syncBeforeSave,0);
  },true);
  document.addEventListener('DOMContentLoaded',function(){[80,300,800,1200,1800,2600,4200].forEach(ms=>setTimeout(install,ms));});
  window.addEventListener('load',function(){[100,500,900,1300,2000,3200,5000].forEach(ms=>setTimeout(install,ms));});
  window.zgSyncEditedMaterials=syncBeforeSave;
})();

(function(){
  'use strict';
  function byId(id){return document.getElementById(id)}
  function clean(v){return String(v==null?'':v).replace(/\s+/g,' ').trim()}
  function equipmentType(){
    const explicit=clean(byId('zgTipoEquipo')?.value);
    if(explicit)return explicit;
    return clean(byId('marcaEquipo')?.value).toUpperCase()==='GENSET'?'Genset':'Reefer';
  }
  window.zgGetEquipmentType=equipmentType;
  const GENSET_WORKS=new Set(['genset_inspeccion_diagnostico','genset_mantenimiento_preventivo','genset_mantenimiento_correctivo','genset_cambio_aceite_filtros','genset_sistema_electrico','genset_prueba_carga']);
  function allowed(w){
    if(w&&w.custom)return true;
    const t=equipmentType();
    if(t==='Genset')return GENSET_WORKS.has(w.id);
    // Para reefer y otros equipos se conservan los trabajos existentes del panel,
    // excepto los trabajos exclusivos del generador.
    return !GENSET_WORKS.has(w.id);
  }
  function renderCardsV8(){
    if(typeof workGrid==='undefined'||!workGrid)return;
    workGrid.innerHTML='';
    const q=typeof normaliza==='function'?normaliza(String(workQuery||'').trim()):String(workQuery||'').toLowerCase();
    const customs=Object.values(state.selected||{}).filter(s=>s.custom&&!WORK_TYPES.find(w=>w.id===s.id));
    const todos=[...WORK_TYPES,...customs].filter(allowed);
    let shown=0;
    todos.forEach(function(w){if(!q||(typeof normaliza==='function'?normaliza(w.nombre):w.nombre.toLowerCase()).includes(q)){workGrid.appendChild(makeWorkCard(w));shown++;}});
    if(q&&!shown){const n=document.createElement('div');n.className='work-none';n.textContent='Sin trabajos que coincidan con "'+String(workQuery||'').trim()+'" para este tipo de equipo.';workGrid.appendChild(n);}
    const add=document.createElement('button');add.type='button';add.className='work-card add-work';add.innerHTML='<span class="ic">＋</span><span>Otro trabajo</span>';add.onclick=addCustomWork;workGrid.appendChild(add);
  }
  renderCardsV8.__zgV8=true;

  function markModeFields(){
    const type=equipmentType(), isG=type==='Genset';
    document.body.classList.toggle('zg-mode-genset',isG);
    document.querySelectorAll('.zg-reefer-pre-field').forEach(el=>{el.classList.toggle('is-hidden',isG);el.querySelectorAll('input,select,textarea').forEach(x=>x.disabled=isG);});
    const gp=byId('zgGensetPreCard');if(gp){gp.classList.toggle('is-hidden',!isG);gp.querySelectorAll('input,select,textarea').forEach(x=>x.disabled=!isG);}
    const sizeWrap=byId('zgTamanoContenedorWrap');if(sizeWrap)sizeWrap.classList.toggle('is-hidden',isG);
    const size=byId('zgTamanoContenedor');if(size){size.disabled=isG;if(isG)size.value='No aplica';}
    document.querySelectorAll('.zg-reefer-final-field').forEach(el=>{el.classList.toggle('is-hidden',isG);el.querySelectorAll('input,select,textarea').forEach(x=>x.disabled=isG);});
    document.querySelectorAll('.zg-genset-final-field').forEach(el=>{el.classList.toggle('is-hidden',!isG);el.querySelectorAll('input,select,textarea').forEach(x=>x.disabled=!isG);});
    const title=document.querySelector('#finalControlCard .final-control-head h3');if(title)title.textContent=isG?'⚡ Control final del generador':'✅ Control final del equipo reefer';
    const desc=document.querySelector('#finalControlCard .final-control-head p');if(desc)desc.textContent=isG?'Registra la condición del motor, la generación eléctrica y las pruebas finales del genset.':'Registra cómo queda el equipo después del trabajo. Esta parte confirma funcionamiento y parámetros finales.';
    const eqLabel=document.querySelector('label[for="equipoNo"]');if(eqLabel)eqLabel.textContent=isG?'N° de genset / equipo':'Contenedor / equipo';
    const eq=byId('equipoNo');if(eq)eq.placeholder=isG?'Escribe o selecciona el N° de genset':'Escribe o selecciona contenedor. Ej. ZGRU01220-7';
    const brand=byId('marcaEquipo');if(isG&&brand){brand.value='GENSET';try{brand.dispatchEvent(new Event('change',{bubbles:true}));}catch(e){}}
    const ref=byId('refrigerante');if(isG&&ref)ref.value='No aplica';
    const obs=byId('observacionInicial');if(obs)obs.placeholder=isG?'Describe cómo se encontró el genset: motor, batería, combustible, fugas, alarmas y condición general.':'Describe cómo se encontró el equipo antes de intervenirlo.';
    try{renderWorkCards=renderCardsV8;}catch(e){window.renderWorkCards=renderCardsV8;}
    try{renderCardsV8();}catch(e){}
  }

  function clearIncompatibleSelected(){
    const bad=Object.values(state.selected||{}).filter(s=>!allowed(s));
    if(!bad.length)return true;
    const hasData=bad.some(s=>(s.photos&&s.photos.length)||clean(s.detalle)||Object.keys(s.campos||{}).some(k=>clean(s.campos[k])));
    if(hasData&&!confirm('Al cambiar el tipo de equipo se quitarán '+bad.length+' trabajo(s) que no corresponden. ¿Continuar?'))return false;
    bad.forEach(s=>delete state.selected[s.id]);
    try{renderPanels();updateCounter();}catch(e){}
    return true;
  }

  function onTypeChange(ev){
    const sel=byId('zgTipoEquipo');if(!sel)return;
    const old=sel.dataset.previousType||'';
    if(!clearIncompatibleSelected()){sel.value=old;return;}
    sel.dataset.previousType=sel.value;
    markModeFields();
  }

  function err(id,text){
    try{if(typeof fieldMsg==='function')return fieldMsg(id,text);}catch(e){}
    const x=byId(id),e=byId(id+'Error');if(x){x.classList.add('input-error');x.scrollIntoView({behavior:'smooth',block:'center'});}if(e){e.textContent=text;e.classList.add('show');}try{toast(text);}catch(_){}return false;
  }
  function value(id){return clean(byId(id)?.value)}
  function validateGensetPre(){
    if(equipmentType()!=='Genset') return true;
    const req=[
      ['gensetHorometroInicial','Registra el horómetro inicial del genset.'],
      ['gensetVoltajeBateriaInicial','Registra el voltaje inicial de la batería.'],
      ['gensetNivelCombustibleInicial','Selecciona el nivel inicial de combustible.'],
      ['gensetNivelAceiteInicial','Selecciona el nivel inicial de aceite.'],
      ['gensetRefrigeranteMotorInicial','Selecciona el estado del refrigerante del motor.'],
      ['gensetArranqueInicial','Selecciona el resultado de la prueba de arranque inicial.']
    ];
    for(const p of req){ if(!value(p[0])) return err(p[0],p[1]); }
    return true;
  }

  function validateGensetFinal(){
    const req=[['gensetEstadoFinal','Selecciona el estado final del genset.'],['gensetHorometroFinal','Registra el horómetro final.'],['gensetArranqueFinal','Selecciona el resultado de la prueba de arranque.'],['gensetVoltajeBateriaFinal','Registra el voltaje final de la batería.'],['gensetVoltajeSalidaL1L2','Registra el voltaje de salida L1-L2.'],['gensetVoltajeSalidaL2L3','Registra el voltaje de salida L2-L3.'],['gensetVoltajeSalidaL1L3','Registra el voltaje de salida L1-L3.'],['gensetTemperaturaMotorFinal','Registra la temperatura final del motor.'],['gensetNivelCombustibleFinal','Selecciona el nivel final de combustible.']];
    for(const p of req){if(!value(p[0]))return err(p[0],p[1]);}
    const r=value('zgRequiereOtroMantenimiento');if(!r)return err('zgRequiereOtroMantenimiento','Indica si el genset requiere otro mantenimiento.');
    if(r==='Sí'){if(!value('zgTipoOtroMantenimiento'))return err('zgTipoOtroMantenimiento','Selecciona el tipo de mantenimiento requerido.');if(value('zgMotivoOtroMantenimiento').length<10)return err('zgMotivoOtroMantenimiento','Explica la razón del mantenimiento requerido con al menos 10 caracteres.');}
    return true;
  }
  function dataFinalV8(){
    if(equipmentType()!=='Genset'){
      return {estadoFinalEquipo:value('estadoFinalEquipo'),setPointFinal:value('setPointFinal'),tempAmbienteFinal:value('tempAmbienteFinal'),presionAltaFinal:value('presionAltaFinal'),presionBajaFinal:value('presionBajaFinal'),retornoFinal:value('retornoFinal'),suministroFinal:value('suministroFinal'),voltajeFinalL1L2:value('voltajeFinalL1L2'),voltajeFinalL2L3:value('voltajeFinalL2L3'),voltajeFinalL1L3:value('voltajeFinalL1L3'),requiereOtroMantenimiento:value('zgRequiereOtroMantenimiento'),tipoOtroMantenimiento:value('zgTipoOtroMantenimiento'),motivoOtroMantenimiento:value('zgMotivoOtroMantenimiento'),tipoEquipo:equipmentType()};
    }
    return {tipoEquipo:'Genset',gensetEstadoFinal:value('gensetEstadoFinal'),gensetHorometroFinal:value('gensetHorometroFinal'),gensetArranqueFinal:value('gensetArranqueFinal'),gensetPruebaCargaFinal:value('gensetPruebaCargaFinal'),gensetVoltajeBateriaFinal:value('gensetVoltajeBateriaFinal'),gensetFrecuenciaFinal:value('gensetFrecuenciaFinal'),gensetVoltajeSalidaL1L2:value('gensetVoltajeSalidaL1L2'),gensetVoltajeSalidaL2L3:value('gensetVoltajeSalidaL2L3'),gensetVoltajeSalidaL1L3:value('gensetVoltajeSalidaL1L3'),gensetPresionAceiteFinal:value('gensetPresionAceiteFinal'),gensetTemperaturaMotorFinal:value('gensetTemperaturaMotorFinal'),gensetNivelCombustibleFinal:value('gensetNivelCombustibleFinal'),requiereOtroMantenimiento:value('zgRequiereOtroMantenimiento'),tipoOtroMantenimiento:value('zgTipoOtroMantenimiento'),motivoOtroMantenimiento:value('zgMotivoOtroMantenimiento')};
  }
  function installOverrides(){
    try{
      if(typeof validarInspeccionPreliminar==='function'&&!validarInspeccionPreliminar.__zgV9){
        const oldPre=validarInspeccionPreliminar;
        const fnPre=function(){ return oldPre() && validateGensetPre(); };
        fnPre.__zgV9=true;
        validarInspeccionPreliminar=fnPre;
      }
      if(typeof validarControlFinal==='function'&&!validarControlFinal.__zgV8){const old=validarControlFinal;const fn=function(){return equipmentType()==='Genset'?validateGensetFinal():old();};fn.__zgV8=true;validarControlFinal=fn;}
      if(typeof datosControlFinal==='function'&&!datosControlFinal.__zgV8){const fn=dataFinalV8;fn.__zgV8=true;datosControlFinal=fn;}
    }catch(e){console.warn('V9 overrides',e)}
  }

  function clearV8Final(){
    ['gensetEstadoFinal','gensetHorometroFinal','gensetArranqueFinal','gensetPruebaCargaFinal','gensetVoltajeBateriaFinal','gensetFrecuenciaFinal','gensetVoltajeSalidaL1L2','gensetVoltajeSalidaL2L3','gensetVoltajeSalidaL1L3','gensetPresionAceiteFinal','gensetTemperaturaMotorFinal','gensetNivelCombustibleFinal'].forEach(id=>{const x=byId(id);if(x){x.value='';x.classList.remove('input-error');}const e=byId(id+'Error');if(e){e.textContent='';e.classList.remove('show');}});
  }
  const oldClearFinal=window.zgClearFinalControlFields;
  window.zgClearFinalControlFields=function(){try{if(typeof oldClearFinal==='function')oldClearFinal();}finally{clearV8Final();}};

  function init(){
    const sel=byId('zgTipoEquipo');if(sel&&!sel.dataset.zgV8){sel.dataset.zgV8='1';sel.dataset.previousType=sel.value||'';sel.addEventListener('change',onTypeChange);}
    const brand=byId('marcaEquipo');if(brand&&!brand.dataset.zgV8){brand.dataset.zgV8='1';brand.addEventListener('change',function(){if(clean(brand.value).toUpperCase()==='GENSET'&&sel&&sel.value!=='Genset'){sel.value='Genset';onTypeChange();}});}
    installOverrides();markModeFields();
    setTimeout(function(){installOverrides();markModeFields();},300);
    setTimeout(function(){installOverrides();markModeFields();},1000);
  }
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init,{once:true});else init();
  window.addEventListener('load',init,{once:true});
})();

(function(){
  'use strict';
  const byId=id=>document.getElementById(id);
  const clean=v=>String(v==null?'':v).replace(/\s+/g,' ').trim();
  const norm=v=>clean(v).toUpperCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'');
  const unique=a=>Array.from(new Set(a.filter(Boolean)));
  const type=()=>clean(byId('zgTipoEquipo')?.value);
  const models=()=>type()==='Genset'?(Array.isArray(MODELOS_GENSET_CATALOGO)?MODELOS_GENSET_CATALOGO:[]): (Array.isArray(MODELOS_REEFER_CATALOGO)?MODELOS_REEFER_CATALOGO:[]);

  function controllersFor(brand){return unique(models().filter(m=>norm(m.marca_equipo)===norm(brand)).map(m=>clean(m.controlador)));}
  function refreshMain(){
    const brand=byId('marcaEquipo'),ctrl=byId('controladorEquipo'),list=byId('controladorOpciones'),hint=byId('controladorHint');if(!brand||!ctrl||!list)return;
    const oldBrand=clean(brand.value),oldCtrl=clean(ctrl.value);
    const brands=unique(models().map(m=>clean(m.marca_equipo)));
    brand.innerHTML='<option value="">Seleccionar</option>'+brands.map(b=>'<option value="'+b.replace(/"/g,'&quot;')+'">'+b+'</option>').join('');
    if(brands.includes(oldBrand))brand.value=oldBrand;else if(type()==='Genset'&&brands.length===1)brand.value=brands[0];
    const ctrls=controllersFor(brand.value);list.innerHTML='';ctrls.forEach(c=>{const o=document.createElement('option');o.value=c;list.appendChild(o);});
    if(!ctrls.includes(oldCtrl))ctrl.value=ctrls.length===1?ctrls[0]:'';else ctrl.value=oldCtrl;
    ctrl.placeholder=ctrls.length?'Selecciona o escribe: '+ctrls.join(' / '):'Escribe controlador';
    if(hint)hint.textContent=ctrls.length?'Controladores registrados en el panel: '+ctrls.join(', '):'Crea la marca y su controlador en el panel.';
  }
  function refreshTunnel(){
    for(let i=1;i<=5;i++){
      const brand=byId('zgMachineBrand'+i),ctrl=byId('zgMachineController'+i),list=byId('zgMachineControllerList'+i);if(!brand||!ctrl||!list)continue;
      const oldB=clean(brand.value),oldC=clean(ctrl.value),brands=unique((Array.isArray(MODELOS_REEFER_CATALOGO)?MODELOS_REEFER_CATALOGO:[]).map(m=>clean(m.marca_equipo)));
      brand.innerHTML='<option value="">Seleccionar</option>'+brands.map(b=>'<option>'+b+'</option>').join('');if(brands.includes(oldB))brand.value=oldB;
      const cs=unique((Array.isArray(MODELOS_REEFER_CATALOGO)?MODELOS_REEFER_CATALOGO:[]).filter(m=>norm(m.marca_equipo)===norm(brand.value)).map(m=>clean(m.controlador)));list.innerHTML='';cs.forEach(c=>{const o=document.createElement('option');o.value=c;list.appendChild(o);});if(!cs.includes(oldC))ctrl.value='';
      if(!brand.dataset.zgV12){brand.dataset.zgV12='1';brand.addEventListener('change',refreshTunnel);}
    }
  }
  function keyFor(marca,ctrl){const b=norm(marca),c=norm(ctrl).replace(/\s+/g,'');if(b==='STAR COOL'&&c.includes('CIM6'))return 'STAR COOL CIM 6';if(b==='STAR COOL'&&c.includes('CIM5'))return 'STAR COOL CIM 5';if(b==='THERMO KING'&&c.includes('MP5000'))return 'TK MP5000';if(b==='THERMO KING'&&c.includes('MP4000'))return 'TK MP4000';if(b==='CARRIER')return 'CARRIER';if(b==='DAIKIN')return 'DAIKIN';return '';}
  function installReeferMaterials(){
    if(!window.ZG_CATALOGOS_POR_CONTROLADOR)return;
    const all=Array.isArray(REPUESTOS_REEFER_CATALOGO)?REPUESTOS_REEFER_CATALOGO:[];
    ['STAR COOL CIM 6','STAR COOL CIM 5','TK MP5000','TK MP4000','CARRIER','DAIKIN'].forEach(k=>window.ZG_CATALOGOS_POR_CONTROLADOR[k]=[]);
    const groups={};
    all.forEach(r=>{if(norm(r.controlador)==='TODOS')return;const key=keyFor(r.marca_equipo,r.controlador);if(!key)return;(groups[key]||(groups[key]=[])).push({codigo:r.codigo||'',detalle:r.detalle||'',unidad:r.unidad||''});});
    // Materiales marcados TODOS aplican a cualquier controlador de la marca.
    all.filter(r=>norm(r.controlador)==='TODOS').forEach(r=>{const b=norm(r.marca_equipo);const keys=b==='CARRIER'?['CARRIER']:b==='DAIKIN'?['DAIKIN']:b==='THERMO KING'?['TK MP5000','TK MP4000']:b==='STAR COOL'?['STAR COOL CIM 5','STAR COOL CIM 6']:[];keys.forEach(k=>(groups[k]||(groups[k]=[])).push({codigo:r.codigo||'',detalle:r.detalle||'',unidad:r.unidad||''}));});
    Object.keys(groups).forEach(k=>window.ZG_CATALOGOS_POR_CONTROLADOR[k]=groups[k]);
  }
  function genericAlarm(){const a=byId('alarmaEncontrada');if(!a)return;a.removeAttribute('list');a.placeholder='Escribe exactamente lo que aparece en la pantalla. Ej. AL15';const dl=byId('zgAlarmasOpciones');if(dl)dl.remove();}
  function bind(){
    genericAlarm();installReeferMaterials();refreshMain();refreshTunnel();
    const brand=byId('marcaEquipo');if(brand&&!brand.dataset.zgV12){brand.dataset.zgV12='1';brand.addEventListener('change',()=>setTimeout(refreshMain,0));}
    const t=byId('zgTipoEquipo');if(t&&!t.dataset.zgCatalogV12){t.dataset.zgCatalogV12='1';t.addEventListener('change',()=>setTimeout(()=>{refreshMain();refreshTunnel();},20));}
  }
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',bind);else bind();
  window.addEventListener('load',()=>{bind();[300,900,1800].forEach(ms=>setTimeout(bind,ms));});
})();

(function(){
  'use strict';
  function clean(v){ return String(v == null ? '' : v).replace(/\s+/g,'').toUpperCase(); }
  function purge(){
    document.querySelectorAll('option').forEach(function(op){
      if(clean(op.value || op.textContent) === 'MP400') op.remove();
    });
    ['controladorEquipo','zgMachineController1','zgMachineController2','zgMachineController3','zgMachineController4','zgMachineController5'].forEach(function(id){
      var el=document.getElementById(id);
      if(el && clean(el.value)==='MP400'){ el.value=''; el.dispatchEvent(new Event('change',{bubbles:true})); }
    });
  }
  document.addEventListener('DOMContentLoaded',function(){ purge(); setTimeout(purge,250); setTimeout(purge,900); });
  window.addEventListener('load',purge);
  new MutationObserver(purge).observe(document.documentElement,{childList:true,subtree:true});
})();

(function(){
  function apply(){
    var s=document.getElementById('zgTamanoContenedor');
    if(s){
      var current=String(s.value||'').trim();
      var allowed=['','10 pies','20 pies','40 pies'];
      Array.from(s.options).forEach(function(o){if(!allowed.includes(o.value))o.remove();});
      if(!Array.from(s.options).some(function(o){return o.value==='10 pies';})){
        [['10 pies','10 pies'],['20 pies','20 pies'],['40 pies','40 pies']].forEach(function(x){var o=document.createElement('option');o.value=x[0];o.textContent=x[1];s.appendChild(o);});
      }
      if(!allowed.includes(current))s.value='';
    }
  }
  document.addEventListener('DOMContentLoaded',apply);
  window.addEventListener('load',apply,{once:true});
})();

(function(){
  'use strict';
  function byId(id){return document.getElementById(id);}
  function limpiarAyudas(){
    document.querySelectorAll('.zg-redactor-help').forEach(function(help){
      const id=String(help.dataset.zgFor||'').trim();
      const target=id?byId(id):null;
      const field=target&&target.closest?target.closest('.field'):null;
      if(!target||!field||help.closest('.field')!==field){
        if(target) delete target.dataset.zgRedactorOk;
        help.remove();
        return;
      }
      help.hidden=!!(target.disabled||target.hidden||target.closest('[hidden],.is-hidden'));
    });
  }
  function repararTodo(){
    try{
      if(typeof window.zgRepararCodigosMateriales==='function' && window.zgRepuestosTablaFinal && typeof window.zgRepuestosTablaFinal.materiales==='function')
        window.zgRepararCodigosMateriales(window.zgRepuestosTablaFinal.materiales());
      if(typeof window.syncRepuestosManual==='function') window.syncRepuestosManual();
      if(typeof window.renderRepuestosSeleccionados==='function') window.renderRepuestosSeleccionados();
    }catch(e){}
  }
  document.addEventListener('click',function(ev){
    if(ev.target&&ev.target.closest&&ev.target.closest('#repuestoNoBtn')){
      setTimeout(function(){repararTodo(); limpiarAyudas();},40);
    }
    if(ev.target&&ev.target.closest&&(ev.target.closest('#pdfBtn')||ev.target.closest('#preBtn'))){
      repararTodo(); limpiarAyudas();
    }
  },true);
  const mo=new MutationObserver(function(){limpiarAyudas();});
  function init(){
    limpiarAyudas();
    repararTodo();
    try{mo.observe(document.body,{childList:true,subtree:true});}catch(e){}
  }
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init,{once:true});else init();
  window.addEventListener('load',function(){setTimeout(init,300);setTimeout(repararTodo,1000);},{once:true});
})();

(function(){
  if(typeof ZG_EDIT_MODE === 'undefined' || !ZG_EDIT_MODE) return;
  const fecha = document.getElementById('fecha');
  if(!fecha) return;
  function guardarFechaElegida(){
    const valor = String(fecha.value || '').trim();
    if(!valor) return;
    window.ZG_FECHA_PDF_ACTUAL = valor;
    window.ZG_FECHA_EDICION_CONFIRMADA = valor;
  }
  fecha.addEventListener('input', guardarFechaElegida, true);
  fecha.addEventListener('change', guardarFechaElegida, true);
  // pointerdown/click en captura se ejecuta antes del manejador que genera el PDF.
  document.addEventListener('pointerdown', function(ev){
    if(ev.target && ev.target.closest && ev.target.closest('#pdfBtn')) guardarFechaElegida();
  }, true);
  document.addEventListener('click', function(ev){
    if(ev.target && ev.target.closest && ev.target.closest('#pdfBtn')) guardarFechaElegida();
  }, true);
})();

(function(){
  if(typeof ZG_EDIT_MODE === 'undefined' || !ZG_EDIT_MODE) return;
  const campo = document.getElementById('fecha');
  if(!campo) return;
  let elegida = String(campo.value || '').trim();
  function guardar(){
    const v = String(campo.value || '').trim();
    if(v){
      elegida = v;
      window.ZG_FECHA_PDF_ACTUAL = v;
      window.ZG_FECHA_EDICION_CONFIRMADA = v;
    }
  }
  campo.addEventListener('input', guardar, true);
  campo.addEventListener('change', guardar, true);
  document.addEventListener('pointerdown', function(ev){
    if(ev.target && ev.target.closest && ev.target.closest('#pdfBtn')) guardar();
  }, true);
  document.addEventListener('click', function(ev){
    if(ev.target && ev.target.closest && ev.target.closest('#pdfBtn')){
      if(elegida){
        campo.value = elegida;
        campo.setAttribute('value', elegida);
        window.ZG_FECHA_PDF_ACTUAL = elegida;
        window.ZG_FECHA_EDICION_CONFIRMADA = elegida;
      }
    }
  }, true);
})();

(function(){
  function byId(id){return document.getElementById(id)}
  function clean(v){return String(v==null?'':v).trim()}
  function pad(n){return String(n).padStart(2,'0')}
  function sqlToLocal(value){
    value=clean(value); if(!value)return '';
    const m=value.match(/^(\d{4})-(\d{2})-(\d{2})[ T](\d{2}):(\d{2})(?::(\d{2}))?/);
    return m ? `${m[1]}-${m[2]}-${m[3]}T${m[4]}:${m[5]}` : value.slice(0,16).replace(' ','T');
  }
  function nowLocal(){const d=new Date();return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`}
  function formatHuman(value){
    value=clean(value); if(!value)return '';
    const m=value.match(/^(\d{4})-(\d{2})-(\d{2})[ T](\d{2}):(\d{2})/);
    return m ? `${m[3]}/${m[2]}/${m[1]} ${m[4]}:${m[5]}` : value;
  }
  window.zgSqlToLocalDateTime=sqlToLocal;
  window.zgFormatServiceDateTime=formatHuman;
  function report(){try{return (typeof ZG_EDIT_REPORT!=='undefined'&&ZG_EDIT_REPORT)||null}catch(e){return null}}
  function preliminary(){try{return (typeof PREINSPECCION!=='undefined'&&PREINSPECCION)||null}catch(e){return null}}
  function isEdit(){try{return typeof ZG_EDIT_MODE!=='undefined'&&!!ZG_EDIT_MODE}catch(e){return false}}
  function init(){
    const ini=byId('horaInicioServicio'), fin=byId('horaFinServicio'); if(!ini||!fin)return;
    const r=report(), p=preliminary();
    const start=clean((r&&r.hora_inicio_servicio)||(p&&p.hora_inicio_servicio)||(p&&p.creado_en));
    const end=clean((r&&r.hora_fin_servicio)||(p&&p.hora_fin_servicio)||(p&&p.finalizado_en));
    if(start&&!clean(ini.value))ini.value=sqlToLocal(start);
    if(end&&!clean(fin.value))fin.value=sqlToLocal(end);
    if(isEdit()){
      [ini,fin].forEach(function(el){el.readOnly=false;el.removeAttribute('aria-readonly');el.classList.add('zg-time-supervisor-edit')});
      document.querySelectorAll('.zg-service-time-hint').forEach(function(x){x.textContent='Editable únicamente desde supervisión. Al actualizar se reemplazará el PDF.'});
      if(new URLSearchParams(location.search).get('editar_horario')==='1') setTimeout(function(){byId('finalControlCard')?.scrollIntoView({behavior:'smooth',block:'start'});ini.focus()},350);
    }else{
      [ini,fin].forEach(function(el){el.readOnly=true;el.setAttribute('aria-readonly','true')});
    }
  }
  window.zgPrepareServiceTimesForFinalPdf=function(){
    const ini=byId('horaInicioServicio'), fin=byId('horaFinServicio');
    const p=preliminary();
    if(ini&&!clean(ini.value)) ini.value=sqlToLocal(clean(p&&p.hora_inicio_servicio)||clean(p&&p.creado_en)||nowLocal());
    if(fin&&!clean(fin.value)&&!isEdit()) fin.value=nowLocal();
    return {inicio:ini?ini.value:'',fin:fin?fin.value:''};
  };
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init);else init();
  window.addEventListener('load',function(){setTimeout(init,150);setTimeout(init,800)});
})();

(function(){
  const signedIds=['setPoint','temperaturaAmbiente','retornoAire','suministroAire','setPointFinal','tempAmbienteFinal','retornoFinal','suministroFinal'];
  function normalizeDecimal(v){ return String(v==null?'':v).replace(/,/g,'.').replace(/[^0-9.\-]/g,''); }
  function refresh(btn,input){ btn.classList.toggle('is-negative', String(input.value||'').trim().startsWith('-')); }
  function installSignedInput(id){
    const input=document.getElementById(id);
    if(!input || input.dataset.zgSignedReady==='1') return;
    input.dataset.zgSignedReady='1';
    input.type='text';
    input.setAttribute('inputmode','decimal');
    input.setAttribute('autocomplete','off');
    input.setAttribute('autocorrect','off');
    input.setAttribute('spellcheck','false');
    const parent=input.parentNode;
    const wrap=document.createElement('div');
    wrap.className='zg-signed-input-wrap';
    parent.insertBefore(wrap,input);
    wrap.appendChild(input);
    const btn=document.createElement('button');
    btn.type='button';
    btn.className='zg-sign-toggle';
    btn.textContent='−';
    btn.title='Cambiar entre valor positivo y negativo';
    btn.setAttribute('aria-label','Cambiar signo del valor');
    wrap.appendChild(btn);
    btn.addEventListener('click',function(){
      let v=normalizeDecimal(input.value).trim();
      if(v.startsWith('-')) v=v.slice(1);
      else v='-'+v;
      input.value=v;
      refresh(btn,input);
      input.dispatchEvent(new Event('input',{bubbles:true}));
      input.dispatchEvent(new Event('change',{bubbles:true}));
      input.focus({preventScroll:true});
      try{ input.setSelectionRange(input.value.length,input.value.length); }catch(e){}
    });
    input.addEventListener('input',function(){
      const before=input.value;
      const neg=before.trim().startsWith('-');
      let cleaned=normalizeDecimal(before);
      cleaned=cleaned.replace(/(?!^)-/g,'');
      const firstDot=cleaned.indexOf('.');
      if(firstDot>=0) cleaned=cleaned.slice(0,firstDot+1)+cleaned.slice(firstDot+1).replace(/\./g,'');
      if(neg && !cleaned.startsWith('-')) cleaned='-'+cleaned.replace(/-/g,'');
      if(cleaned!==before) input.value=cleaned;
      refresh(btn,input);
    });
    refresh(btn,input);
  }
  function install(){ signedIds.forEach(installSignedInput); }
  if(document.readyState==='loading') document.addEventListener('DOMContentLoaded',install); else install();
  window.addEventListener('load',install);
  // Algunos bloques se vuelven a dibujar al cambiar el tipo de equipo.
  new MutationObserver(install).observe(document.documentElement,{childList:true,subtree:true});
})();

(function(){
  function actualizarAyudaDespuesDeGuardar(){
    var box=document.getElementById('savedBox');
    var guardado=!!(box && box.classList.contains('show'));
    document.body.classList.toggle('zg-informe-guardado',guardado);
    if(guardado){
      document.querySelectorAll('.zg-redactor-help').forEach(function(el){
        el.setAttribute('aria-hidden','true');
      });
    }
  }
  function iniciar(){
    var box=document.getElementById('savedBox');
    if(box){
      try{
        new MutationObserver(actualizarAyudaDespuesDeGuardar).observe(box,{attributes:true,attributeFilter:['class'],childList:true,subtree:true});
      }catch(e){}
    }
    actualizarAyudaDespuesDeGuardar();
  }
  if(document.readyState==='loading') document.addEventListener('DOMContentLoaded',iniciar); else iniciar();
  window.addEventListener('load',actualizarAyudaDespuesDeGuardar);
})();

(function(){
  const initialPressureIds=['presionAlta','presionBaja'];
  const finalControlIds=[
    'estadoFinalEquipo','setPointFinal','tempAmbienteFinal','retornoFinal','suministroFinal',
    'presionAltaFinal','presionBajaFinal','voltajeFinalL1L2','voltajeFinalL2L3',
    'voltajeFinalL1L3','zgRequiereOtroMantenimiento','zgTipoOtroMantenimiento','zgMotivoOtroMantenimiento','gensetEstadoFinal','gensetHorometroFinal','gensetArranqueFinal','gensetPruebaCargaFinal','gensetVoltajeBateriaFinal','gensetFrecuenciaFinal','gensetVoltajeSalidaL1L2','gensetVoltajeSalidaL2L3','gensetVoltajeSalidaL1L3','gensetPresionAceiteFinal','gensetTemperaturaMotorFinal','gensetNivelCombustibleFinal','repuestosManual'
  ];
  const freshReportIds=[
    'orden','cliente','tecnicoInput','tecnicoSearch','tecnicoId','direccion','direccionCoords','direccionOrigenOdoo','obs',
    'equipoNo','serialUnidad','marcaEquipo','modeloEquipo','controladorEquipo','anioFabricacion','refrigerante',
    'setPoint','temperaturaAmbiente','retornoAire','suministroAire','presionAlta','presionBaja',
    'voltajeL1L2','voltajeL2L3','voltajeL1L3','estadoInicial','estadoEncendido','estadoEnergia','estadoAlarma','alarmaEncontrada',
    'observacionInicial','preinspeccionId','tokenContinuacion','zgModalidadComercial','zgTipoInstalacion',
    'zgRequiereOtroMantenimiento','zgTipoOtroMantenimiento','zgMotivoOtroMantenimiento','zgMaquinaPreliminarObjetivo',
    'adminTiendaNombre','adminTiendaCargo','firmaTecnico','firmaAdmin'
  ].concat(finalControlIds);

  function clearStorage(){
    try{
      [window.sessionStorage,window.localStorage].forEach(function(storage){
        const keys=[];
        for(let i=0;i<storage.length;i++){
          const k=storage.key(i);
          if(k && (k.indexOf('zg_presiones_pre_')===0 || k.indexOf('zg_post_maint_')===0 || k==='zg_pre_evidencias_pending')) keys.push(k);
        }
        keys.forEach(function(k){storage.removeItem(k);});
      });
      localStorage.removeItem('zgroup_preinspeccion_token');
      localStorage.removeItem('zgroup_preinspeccion_id');
    }catch(e){}
  }

  function hardClear(ids){
    (ids||[]).forEach(function(id){
      const el=document.getElementById(id);
      if(!el) return;
      try{
        if(el.tagName==='SELECT'){
          el.selectedIndex=0;
          el.value='';
        }else{
          el.value='';
          el.defaultValue='';
          el.removeAttribute('value');
          el.setAttribute('autocomplete','off');
        }
        el.classList.remove('input-error','ok');
        const err=document.getElementById(id+'Error');
        if(err){err.textContent='';err.classList.remove('show');}
      }catch(e){}
    });
  }

  function clearFinalOnly(){
    window.__zgBlockPressureRestore=true;
    clearStorage();
    hardClear(finalControlIds);
    if(window.zgActualizarMantenimientoFinal) window.zgActualizarMantenimientoFinal();
  }
  window.zgClearFinalControlFields=clearFinalOnly;

  function isFreshReport(){
    let fresh=false;
    try{
      const u=new URL(location.href);
      fresh=u.searchParams.get('nuevo')==='1' || sessionStorage.getItem('zg_force_new_report')==='1';
    }catch(e){}
    return fresh;
  }

  function clearFreshReport(){
    if(!isFreshReport()) return;
    window.__zgBlockPressureRestore=true;
    clearStorage();
    hardClear(freshReportIds);
    if(window.zgActualizarMantenimientoFinal) window.zgActualizarMantenimientoFinal();
    try{
      if(typeof state==='object' && state){ state.selected={}; state.customSeq=0; }
      if(typeof renderWorkCards==='function') renderWorkCards();
      if(typeof renderPanels==='function') renderPanels();
      if(typeof updateCounter==='function') updateCounter();
      if(typeof setRequiereRepuesto==='function') setRequiereRepuesto(false);
    }catch(e){}
  }

  function finishFreshMode(){
    if(!isFreshReport()) return;
    clearFreshReport();
    try{
      sessionStorage.removeItem('zg_force_new_report');
      const u=new URL(location.href);
      u.searchParams.delete('nuevo');
      u.searchParams.delete('_');
      history.replaceState(null,'',u.pathname+(u.searchParams.toString()?'?'+u.searchParams.toString():'')+u.hash);
    }catch(e){}
  }

  // Limpia inmediatamente y nuevamente después de las cargas tardías de la interfaz.
  if(document.readyState==='loading'){
    document.addEventListener('DOMContentLoaded',function(){
      clearFreshReport();
      [50,220,600].forEach(function(ms){setTimeout(clearFreshReport,ms);});
      setTimeout(finishFreshMode,850);
    });
  }else{
    clearFreshReport();
    [50,220,600].forEach(function(ms){setTimeout(clearFreshReport,ms);});
    setTimeout(finishFreshMode,850);
  }
  window.addEventListener('pageshow',function(ev){ if(ev.persisted) clearFreshReport(); });

})();

(function(){
  'use strict';
  function byId(id){ return document.getElementById(id); }
  function clean(v){ return String(v == null ? '' : v).replace(/\s+/g,' ').trim(); }
  function normalize(v){ return clean(v).toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,''); }
  function fieldWrapFromElement(el){ return el && el.closest ? (el.closest('.field') || el.parentElement) : null; }

  function removeLegacyFinalObservation(){
    const found=[];
    document.querySelectorAll('#observacionFinalEquipo, textarea[name="observacionFinalEquipo"]').forEach(function(el){
      const wrap=fieldWrapFromElement(el); if(wrap) found.push(wrap);
    });
    document.querySelectorAll('#finalControlCard .field').forEach(function(field){
      const label=field.querySelector('label');
      const txt=normalize(label ? label.textContent : '');
      if(txt.includes('observacion final de funcionamiento') || txt==='observacion final') found.push(field);
    });
    Array.from(new Set(found)).forEach(function(field){ field.remove(); });
  }

  function removeLegacyMaintenanceCards(){
    document.querySelectorAll('#zgPostRepuestoMaintenanceCard,.zg-work-followup').forEach(function(x){ x.remove(); });
    document.querySelectorAll('h2,h3,h4').forEach(function(title){
      if(!normalize(title.textContent).includes('requiere otro mantenimiento')) return;
      if(title.closest('#finalControlCard')) return;
      const box=title.closest('section,.card,.zg-post-maint-card,.zg-work-followup,div');
      if(box) box.remove();
    });
  }

  function improveText(txt){
    let s=clean(txt)
      .replace(/\bq\b/gi,'que').replace(/\bxq\b/gi,'porque').replace(/\bpq\b/gi,'porque')
      .replace(/\bsta\b/gi,'está').replace(/\beq\b/gi,'equipo')
      .replace(/\btemp\b/gi,'temperatura').replace(/\brefri\b/gi,'refrigerante');
    if(s && !/[.!?]$/.test(s)) s+='.';
    if(s) s=s.charAt(0).toUpperCase()+s.slice(1);
    return s ? 'Se recomienda programar un mantenimiento adicional debido a lo siguiente: '+s : '';
  }

  function installReasonHelp(){
    const ta=byId('zgMotivoOtroMantenimiento');
    if(!ta) return;
    const field=ta.closest('.field');
    if(!field) return;
    document.querySelectorAll('.zg-redactor-help[data-zg-for="zgMotivoOtroMantenimiento"]').forEach(function(help){
      if(help.closest('.field') !== field) help.remove();
    });
    if(field.querySelector('.zg-redactor-help[data-zg-for="zgMotivoOtroMantenimiento"]')) return;
    const wrap=document.createElement('div');
    wrap.className='zg-redactor-help';
    wrap.dataset.zgFor='zgMotivoOtroMantenimiento';
    wrap.innerHTML='<label><input type="checkbox"> Activar ayuda</label><button type="button" disabled>Mejorar explicación</button><small>Convierte la razón técnica en una explicación clara y profesional para el cliente.</small>';
    const chk=wrap.querySelector('input');
    const btn=wrap.querySelector('button');
    chk.addEventListener('change',function(){ btn.disabled=!chk.checked; });
    btn.addEventListener('click',function(){
      if(!chk.checked) return;
      if(!clean(ta.value)){
        ta.focus();
        try{ if(typeof toast==='function') toast('Escribe primero la razón del mantenimiento.'); }catch(e){}
        return;
      }
      ta.value=improveText(ta.value);
      ta.dispatchEvent(new Event('input',{bubbles:true}));
      try{ if(typeof toast==='function') toast('Explicación mejorada'); }catch(e){}
    });
    const hint=field.querySelector('.field-hint');
    if(hint) hint.insertAdjacentElement('afterend',wrap);
    else ta.insertAdjacentElement('afterend',wrap);
  }

  function setupMaintenanceFields(){
    const card=byId('finalControlCard');
    const grid=card && card.querySelector('.final-grid');
    const req=byId('zgRequiereOtroMantenimiento');
    const type=byId('zgTipoOtroMantenimiento');
    const reason=byId('zgMotivoOtroMantenimiento');
    const reqWrap=req && (req.closest('.zg-final-maint-question') || req.closest('.field'));
    const typeWrap=byId('zgTipoMantenimientoWrap') || (type && type.closest('.field'));
    const reasonWrap=byId('zgMotivoMantenimientoWrap') || (reason && reason.closest('.field'));
    if(!card || !grid || !req || !type || !reason || !reqWrap || !typeWrap || !reasonWrap) return;

    reqWrap.id='zgRequiereOtroMantenimientoWrap';
    reqWrap.classList.add('field','full','zg-final-maint-question');
    typeWrap.id='zgTipoMantenimientoWrap';
    typeWrap.classList.add('field','full','zg-maintenance-type');
    reasonWrap.id='zgMotivoMantenimientoWrap';
    reasonWrap.classList.add('field','full','zg-maintenance-reason');
    if(reqWrap.parentElement !== grid) grid.appendChild(reqWrap);
    if(typeWrap.parentElement !== grid) grid.appendChild(typeWrap);
    if(reasonWrap.parentElement !== grid) grid.appendChild(reasonWrap);

    function setVisibility(clearWhenNo){
      const value=clean(req.value);
      const show=value==='Sí';
      typeWrap.classList.toggle('is-hidden',!show);
      reasonWrap.classList.toggle('is-hidden',!show);
      typeWrap.hidden=!show;
      reasonWrap.hidden=!show;
      type.disabled=!show;
      reason.disabled=!show;
      type.required=show;
      reason.required=show;
      if(clearWhenNo && value==='No'){
        type.value='';
        reason.value='';
      }
      if(show) installReasonHelp();
    }

    if(req.dataset.zgStableMaintenanceBound!=='1'){
      req.dataset.zgStableMaintenanceBound='1';
      req.addEventListener('change',function(){ setVisibility(true); });
    }
    setVisibility(false);
    installReasonHelp();
    card.dataset.zgStableMaintenanceReady='1';
  }

  function cleanupOrphanHelpers(){
    document.querySelectorAll('.zg-redactor-help').forEach(function(help){
      const targetId=help.dataset.zgFor || '';
      if(!targetId) return;
      const target=byId(targetId);
      if(!target || !target.isConnected || target.closest('.field')!==help.closest('.field')) help.remove();
    });
  }

  function run(){
    removeLegacyFinalObservation();
    removeLegacyMaintenanceCards();
    setupMaintenanceFields();
    cleanupOrphanHelpers();
  }

  if(document.readyState==='loading') document.addEventListener('DOMContentLoaded',run,{once:true});
  else run();
  window.addEventListener('load',function(){
    run();
    setTimeout(run,300);
    setTimeout(run,1200);
  },{once:true});
  window.zgActualizarMantenimientoFinal=function(){ setupMaintenanceFields(); };
})();

(function(){
  'use strict';
  function byId(id){ return document.getElementById(id); }
  function clean(v){ return String(v == null ? '' : v).replace(/\s+/g,' ').trim(); }
  function hasPreliminary(){ try{return typeof PREINSPECCION!=='undefined' && !!PREINSPECCION;}catch(e){return false;} }
  function msg(id,text){
    try{ if(typeof fieldMsg==='function') return fieldMsg(id,text); }catch(e){}
    const el=byId(id), err=byId(id+'Error');
    if(el){ el.classList.add('input-error'); try{el.focus();}catch(e){} }
    if(err){err.textContent=text;err.classList.add('show');}
    try{if(typeof toast==='function')toast(text);}catch(e){}
    return false;
  }

  function alarmPartsFromState(raw){
    raw=clean(raw);
    let detail='';
    const m=raw.match(/(?:con\s+alarma)\s*[:\-–]\s*(.+)$/i);
    if(m) detail=clean(m[1]);
    return {detail:detail};
  }

  function updateAlarmVisibility(clearWhenNo){
    const sel=byId('estadoAlarma');
    const wrap=byId('zgAlarmaEncontradaWrap');
    const input=byId('alarmaEncontrada');
    if(!sel || !wrap || !input) return;
    const show=clean(sel.value)==='Con alarma';
    wrap.hidden=!show;
    wrap.classList.toggle('is-hidden',!show);
    input.disabled=!show;
    input.required=show;
    if(!show && clearWhenNo){
      input.value='';
      input.classList.remove('input-error');
      const err=byId('alarmaEncontradaError'); if(err){err.textContent='';err.classList.remove('show');}
    }
  }

  function composeInitialState(){
    const a=clean(byId('estadoEncendido')?.value);
    const b=clean(byId('estadoEnergia')?.value);
    const c=clean(byId('estadoAlarma')?.value);
    const d=clean(byId('alarmaEncontrada')?.value);
    const hidden=byId('estadoInicial');
    if(a && b && c){
      const alarmText=(c==='Con alarma' && d) ? (c+': '+d) : c;
      const value=a+' / '+b+' / '+alarmText;
      if(hidden) hidden.value=value;
      return value;
    }
    return hidden ? clean(hidden.value) : '';
  }

  function restoreAlarmDetail(){
    const hidden=byId('estadoInicial');
    const input=byId('alarmaEncontrada');
    const sel=byId('estadoAlarma');
    if(!hidden || !input || !sel) return;
    const parsed=alarmPartsFromState(hidden.value);
    if(parsed.detail && !clean(input.value)) input.value=parsed.detail;
    if(parsed.detail && !clean(sel.value)) sel.value='Con alarma';
    updateAlarmVisibility(false);
    composeInitialState();
    if(hasPreliminary()){
      input.readOnly=true;
      input.style.background='#eef3f8';
    }
  }

  function installAlarmFeature(){
    const sel=byId('estadoAlarma');
    const input=byId('alarmaEncontrada');
    if(!sel || !input) return;
    if(sel.dataset.zgAlarmV7!=='1'){
      sel.dataset.zgAlarmV7='1';
      sel.addEventListener('change',function(){ updateAlarmVisibility(true); composeInitialState(); });
    }
    if(input.dataset.zgAlarmV7!=='1'){
      input.dataset.zgAlarmV7='1';
      input.addEventListener('input',function(){
        input.classList.remove('input-error');
        const err=byId('alarmaEncontradaError'); if(err){err.textContent='';err.classList.remove('show');}
        composeInitialState();
      });
      input.addEventListener('change',composeInitialState);
    }
    restoreAlarmDetail();

    /* Las funciones originales siguen siendo usadas al guardar y generar PDF.
       Se reemplazan para que el detalle de alarma forme parte del estado inicial. */
    try{ zgroupEstadoInicialCompuesto=composeInitialState; }catch(e){ window.zgroupEstadoInicialCompuesto=composeInitialState; }
    try{
      if(typeof validarEstadoInicialTriple==='function' && !validarEstadoInicialTriple.__zgAlarmV7){
        const old=validarEstadoInicialTriple;
        const next=function(){
          if(!old()) return false;
          if(clean(byId('estadoAlarma')?.value)==='Con alarma' && !clean(byId('alarmaEncontrada')?.value)){
            return msg('alarmaEncontrada','Selecciona o escribe la alarma encontrada en el equipo.');
          }
          composeInitialState();
          return true;
        };
        next.__zgAlarmV7=true;
        validarEstadoInicialTriple=next;
      }
    }catch(e){}
  }

  function clearCanvas(id){
    const canvas=byId(id); if(!canvas) return;
    try{
      const ctx=canvas.getContext('2d');
      ctx.clearRect(0,0,canvas.width,canvas.height);
      ctx.fillStyle='#fff';ctx.fillRect(0,0,canvas.width,canvas.height);
    }catch(e){}
    const box=canvas.closest('.firma-box'); if(box) box.classList.remove('firmado');
  }

  function clearGlobalMaterials(){
    const ta=byId('repuestosManual'); if(ta) ta.value='';
    try{
      const api=window.zgRepuestosTablaFinal;
      if(api && typeof api.materiales==='function'){
        const arr=api.materiales(); if(Array.isArray(arr)) arr.splice(0,arr.length);
        if(typeof api.guardar==='function') api.guardar();
        if(typeof api.pintar==='function') api.pintar();
      }
    }catch(e){}
    try{window.repuestosSeleccionados=[];}catch(e){}
    try{if(typeof setRequiereRepuesto==='function')setRequiereRepuesto(false);}catch(e){}
    const search=byId('repuestoSearch'); if(search) search.value='';
    const menu=byId('repuestoSuggest'); if(menu){menu.innerHTML='';menu.classList.remove('show');}
  }
  window.zgClearAllSelectedMaterials=clearGlobalMaterials;

  function clearPostPreliminary(){
    const saved=byId('savedBox'); if(saved) saved.classList.remove('show');
    try{if(typeof setVal==='function')setVal('obs','');else if(byId('obs'))byId('obs').value='';}catch(e){}
    try{if(typeof window.zgClearFinalControlFields==='function')window.zgClearFinalControlFields();}catch(e){}
    try{
      if(typeof MANUAL_REPORT_IDS!=='undefined') MANUAL_REPORT_IDS.forEach(function(id){const el=byId(id);if(el)el.value='';});
    }catch(e){}
    clearGlobalMaterials();

    ['adminTiendaNombre','adminTiendaCargo','firmaTecnico','firmaAdmin'].forEach(function(id){const el=byId(id);if(el)el.value='';});
    clearCanvas('firmaTecnicoCanvas');
    clearCanvas('firmaAdminCanvas');

    try{
      if(typeof state==='object' && state){ state.selected={}; state.customSeq=0; }
      if(typeof renderWorkCards==='function')renderWorkCards();
      if(typeof renderPanels==='function')renderPanels();
      if(typeof updateCounter==='function')updateCounter();
    }catch(e){}
    const workSearch=byId('workSearch'); if(workSearch) workSearch.value='';
    try{if(window.zgClearMaintenanceStorage)window.zgClearMaintenanceStorage();}catch(e){}
    try{window.__zgroupInformeFinalGenerado=false;}catch(e){}
    document.body.classList.remove('zg-informe-guardado');
    try{if(typeof toast==='function')toast('Se limpió todo el cierre. La inspección preliminar se conserva.');}catch(e){}
  }

  /* Se ejecuta antes del listener antiguo del botón. Conserva exclusivamente la preliminar. */
  document.addEventListener('click',function(ev){
    const btn=ev.target && ev.target.closest ? ev.target.closest('#clearBtn') : null;
    if(!btn || !hasPreliminary()) return;
    ev.preventDefault();
    ev.stopPropagation();
    if(ev.stopImmediatePropagation) ev.stopImmediatePropagation();
    if(!confirm('¿Limpiar todos los trabajos, materiales y datos del cierre?\n\nLa inspección preliminar se conservará.')) return false;
    clearPostPreliminary();
    return false;
  },true);

  function init(){
    installAlarmFeature();
    [120,450,1000,1900].forEach(function(ms){setTimeout(installAlarmFeature,ms);});
  }
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init,{once:true});else init();
  window.addEventListener('load',function(){installAlarmFeature();setTimeout(installAlarmFeature,700);},{once:true});
})();

(function(){
  'use strict';
  const byId=id=>document.getElementById(id);
  const clean=v=>String(v==null?'':v).replace(/\s+/g,' ').trim();
  const REEFER_BRANDS=['THERMO KING','CARRIER','STAR COOL','DAIKIN','OTRO'];
  const REEFER_CONTROLLERS={
    'THERMO KING':['MP3000','MP4000','MP5000'],
    'CARRIER':['MICROLINK 2I','MICROLINK 3','MICROLINK 5'],
    'STAR COOL':['CIM5','CIM6'],
    'DAIKIN':['DAIKIN'],
    'OTRO':[]
  };
  const GENERATOR_BRANDS=['THERMO KING'];
  const GENERATOR_CONTROLLERS=['SG-3000','SG-5000'];
  const GENERATOR_WORKS=new Set(['genset_mantenimiento_preventivo','genset_mantenimiento_correctivo']);
  let previousType='';
  let pendingType='';
  let typeSelect=null;

  function equipmentType(){return clean(byId('zgTipoEquipo')?.value);}
  window.zgGetEquipmentType=equipmentType;

  function labelType(v){return v==='Genset'?'generador (genset)':v==='Reefer'?'máquina reefer':'tipo de equipo';}
  function clearError(id){
    const x=byId(id),e=byId(id+'Error');
    if(x)x.classList.remove('input-error','ok');
    if(e){e.textContent='';e.classList.remove('show');}
  }
  function markError(id,msg){
    try{if(typeof fieldMsg==='function')return fieldMsg(id,msg);}catch(e){}
    const x=byId(id),er=byId(id+'Error');
    if(x){x.classList.add('input-error');x.scrollIntoView({behavior:'smooth',block:'center'});setTimeout(()=>{try{x.focus()}catch(_){}},120);}
    if(er){er.textContent=msg;er.classList.add('show');}
    try{if(typeof toast==='function')toast(msg);else alert(msg);}catch(e){alert(msg)}
    return false;
  }
  function setOptions(select,values,current){
    if(!select)return;
    const keep=clean(current||select.value);
    select.innerHTML='<option value="">Seleccionar</option>';
    values.forEach(v=>{const o=document.createElement('option');o.value=v;o.textContent=v;select.appendChild(o);});
    select.value=values.includes(keep)?keep:'';
  }
  function controllerOptions(){
    const type=equipmentType();
    if(type==='Genset')return GENERATOR_CONTROLLERS.slice();
    return (REEFER_CONTROLLERS[clean(byId('marcaEquipo')?.value).toUpperCase()]||[]).slice();
  }
  function actualizarControladorV10(force=false){
    const input=byId('controladorEquipo'),list=byId('controladorOpciones'),hint=byId('controladorHint');
    if(!input||!list)return;
    const options=controllerOptions(),actual=clean(input.value).toUpperCase();
    list.innerHTML='';options.forEach(v=>{const o=document.createElement('option');o.value=v;list.appendChild(o);});
    input.placeholder=options.length?'Selecciona: '+options.join(' / '):'Selecciona o escribe controlador';
    if(force && actual && !options.includes(actual))input.value='';
    if(force && options.length===1)input.value=options[0];
    if(hint){
      hint.textContent=equipmentType()==='Genset'
        ? 'Controladores disponibles para generador: SG-3000 y SG-5000.'
        : (options.length?'Opciones sugeridas para '+(clean(byId('marcaEquipo')?.value)||'la marca')+': '+options.join(', '):'Selecciona la marca para ver controladores sugeridos.');
    }
  }
  window.actualizarOpcionesControlador=actualizarControladorV10;
  try{actualizarOpcionesControlador=actualizarControladorV10;}catch(e){}

  function configureBrandController(){
    const type=equipmentType(),brand=byId('marcaEquipo');
    if(!brand)return;
    const current=clean(brand.value).toUpperCase();
    if(type==='Genset'){
      setOptions(brand,GENERATOR_BRANDS,current);
      if(!brand.value)brand.value='THERMO KING';
    }else if(type==='Reefer'){
      setOptions(brand,REEFER_BRANDS,current==='GENSET'?'':current);
    }else setOptions(brand,REEFER_BRANDS,'');
    actualizarControladorV10(true);
  }

  function allowedWork(w){
    if(w&&w.custom)return !!equipmentType();
    const id=clean(w&&w.id);
    if(!equipmentType())return false;
    if(equipmentType()==='Genset')return GENERATOR_WORKS.has(id);
    return !GENERATOR_WORKS.has(id) && !/genset/i.test(id);
  }
  function renderCardsV10(){
    try{
      if(typeof workGrid==='undefined'||!workGrid)return;
      workGrid.innerHTML='';
      if(!equipmentType()){
        const n=document.createElement('div');n.className='work-none';n.textContent='Selecciona primero si se atenderá una máquina reefer o un generador.';workGrid.appendChild(n);return;
      }
      const q=typeof normaliza==='function'?normaliza(String(workQuery||'').trim()):String(workQuery||'').toLowerCase();
      const customs=Object.values(state.selected||{}).filter(s=>s.custom&&!WORK_TYPES.find(w=>w.id===s.id));
      const todos=[...WORK_TYPES,...customs].filter(allowedWork);
      let shown=0;
      todos.forEach(w=>{if(!q||(typeof normaliza==='function'?normaliza(w.nombre):String(w.nombre).toLowerCase()).includes(q)){workGrid.appendChild(makeWorkCard(w));shown++;}});
      if(q&&!shown){const n=document.createElement('div');n.className='work-none';n.textContent='No hay trabajos que coincidan para este tipo de equipo.';workGrid.appendChild(n);}
      const add=document.createElement('button');add.type='button';add.className='work-card add-work';add.innerHTML='<span class="ic">＋</span><span>Otro trabajo</span>';add.onclick=addCustomWork;workGrid.appendChild(add);
    }catch(e){console.warn('renderCardsV10',e)}
  }

  function setDisabled(selector,disabled){document.querySelectorAll(selector).forEach(el=>{el.disabled=!!disabled;});}
  function applyMode(){
    const type=equipmentType(),isR=type==='Reefer',isG=type==='Genset',chosen=isR||isG;
    if(!previousType&&chosen)previousType=type;
    document.body.classList.toggle('zg-mode-reefer',isR);
    document.body.classList.toggle('zg-mode-genset',isG);
    byId('zgCommonServiceConfig')?.classList.toggle('is-hidden',!chosen);
    byId('zgReeferConfig')?.classList.toggle('is-hidden',!isR);
    const details=byId('zgEquipmentDetails');if(details)details.classList.toggle('is-hidden',!chosen);
    const finalCard=byId('finalControlCard');if(finalCard)finalCard.classList.toggle('zg-type-hidden',!chosen);
    setDisabled('#zgEquipmentDetails input,#zgEquipmentDetails select,#zgEquipmentDetails textarea',!chosen);
    if(chosen){
      setDisabled('.zg-reefer-pre-field input,.zg-reefer-pre-field select,.zg-reefer-pre-field textarea',!isR);
      setDisabled('#zgGensetPreCard input,#zgGensetPreCard select,#zgGensetPreCard textarea',!isG);
      setDisabled('.zg-reefer-final-field input,.zg-reefer-final-field select,.zg-reefer-final-field textarea',!isR);
      setDisabled('.zg-genset-final-field input,.zg-genset-final-field select,.zg-genset-final-field textarea',!isG);
      ['zgRequiereOtroMantenimiento','zgTipoOtroMantenimiento','zgMotivoOtroMantenimiento'].forEach(id=>{const x=byId(id);if(x)x.disabled=false;});
    }
    const common=byId('zgModalidadComercial');if(common)common.disabled=!chosen;
    const install=byId('zgTipoInstalacion');if(install)install.disabled=!isR;
    const size=byId('zgTamanoContenedor');if(size){size.disabled=!isR;if(isG)size.value='No aplica';}
    const tunnel=isR&&clean(install?.value)==='Túnel';
    byId('zgTunnelConfig')?.classList.toggle('is-hidden',!tunnel);
    const eqLabel=document.querySelector('label[for="equipoNo"]');if(eqLabel)eqLabel.textContent=isG?'N° de generador / equipo':'Contenedor / equipo';
    const eq=byId('equipoNo');if(eq)eq.placeholder=isG?'Escribe o selecciona el N° del generador':'Escribe o selecciona contenedor. Ej. ZGRU01220-7';
    const title=document.querySelector('#finalControlCard .final-control-head h3');if(title)title.textContent=isG?'⚡ Control final del generador':'✅ Control final de la máquina reefer';
    const desc=document.querySelector('#finalControlCard .final-control-head p');if(desc)desc.textContent=isG?'Registra la condición del motor, la generación eléctrica y las pruebas finales del generador.':'Registra cómo queda la máquina reefer después del trabajo.';
    const obs=byId('observacionInicial');if(obs)obs.placeholder=isG?'Describe cómo se encontró el generador: motor, batería, combustible, fugas, alarmas y condición general.':'Describe cómo se encontró la máquina reefer antes de intervenirla.';
    configureBrandController();
    try{renderWorkCards=renderCardsV10;}catch(e){window.renderWorkCards=renderCardsV10;}
    renderCardsV10();
    try{if(typeof renderPanels==='function')renderPanels();if(typeof updateCounter==='function')updateCounter();}catch(e){}
  }

  const dataIds=[
    'zgModalidadComercial','zgTipoInstalacion','zgTamanoContenedor','zgMaquinaPreliminarObjetivo',
    'equipoNo','serialUnidad','marcaEquipo','modeloEquipo','controladorEquipo','anioFabricacion','refrigerante','setPoint','temperaturaAmbiente','retornoAire','suministroAire','presionAlta','presionBaja','voltajeL1L2','voltajeL2L3','voltajeL1L3',
    'estadoEncendido','estadoEnergia','estadoAlarma','alarmaEncontrada','estadoInicial','observacionInicial',
    'gensetHorometroInicial','gensetVoltajeBateriaInicial','gensetNivelCombustibleInicial','gensetNivelAceiteInicial','gensetRefrigeranteMotorInicial','gensetArranqueInicial','gensetFrecuenciaInicial','gensetPresionAceiteInicial',
    'estadoFinalEquipo','setPointFinal','retornoFinal','suministroFinal','voltajeFinalL1L2','voltajeFinalL2L3','voltajeFinalL1L3','tempAmbienteFinal','presionAltaFinal','presionBajaFinal',
    'gensetEstadoFinal','gensetHorometroFinal','gensetArranqueFinal','gensetPruebaCargaFinal','gensetVoltajeBateriaFinal','gensetFrecuenciaFinal','gensetVoltajeSalidaL1L2','gensetVoltajeSalidaL2L3','gensetVoltajeSalidaL1L3','gensetPresionAceiteFinal','gensetTemperaturaMotorFinal','gensetNivelCombustibleFinal',
    'zgRequiereOtroMantenimiento','zgTipoOtroMantenimiento','zgMotivoOtroMantenimiento','repuestosManual'
  ];
  function hasTypeData(){
    if(dataIds.some(id=>clean(byId(id)?.value)))return true;
    for(let i=1;i<=5;i++)if(['Brand','Controller','Serial'].some(k=>clean(byId('zgMachine'+k+i)?.value)))return true;
    try{if(typeof state==='object'&&state&&Object.keys(state.selected||{}).length)return true;}catch(e){}
    try{if(Array.isArray(window.ZG_PRE_EVIDENCIAS)&&window.ZG_PRE_EVIDENCIAS.length)return true;}catch(e){}
    return false;
  }
  function clearField(id){const x=byId(id);if(!x)return;if(x.type==='checkbox'||x.type==='radio')x.checked=false;else x.value='';clearError(id);}
  function clearTypeData(){
    dataIds.forEach(clearField);
    for(let i=1;i<=5;i++)['Brand','Controller','Serial'].forEach(k=>clearField('zgMachine'+k+i));
    try{if(typeof state==='object'&&state){state.selected={};state.customSeq=0;}}catch(e){}
    try{if(typeof window.zgClearAllSelectedMaterials==='function')window.zgClearAllSelectedMaterials();}catch(e){}
    try{window.ZG_PRE_EVIDENCIAS=[];if(typeof window.renderPreEvidenceGrid==='function')window.renderPreEvidenceGrid();}catch(e){}
    try{if(typeof window.zgClearFinalControlFields==='function')window.zgClearFinalControlFields();}catch(e){}
    ['firmaTecnico','firmaAdmin','adminTiendaNombre','adminTiendaCargo'].forEach(clearField);
    try{if(typeof renderPanels==='function')renderPanels();if(typeof updateCounter==='function')updateCounter();}catch(e){}
    const search=byId('workSearch');if(search)search.value='';
  }

  function ensureSwitchModal(){
    let modal=byId('zgTypeSwitchModal');if(modal)return modal;
    modal=document.createElement('div');modal.id='zgTypeSwitchModal';modal.className='zg-type-switch-modal';
    modal.innerHTML='<div class="zg-type-switch-box"><div class="zg-type-switch-head"><div class="zg-type-switch-icon">⚠️</div><h3>Cambiar el tipo de equipo</h3></div><div class="zg-type-switch-body"><p id="zgTypeSwitchText"></p><p style="margin-top:10px"><strong>Se borrarán los datos técnicos, trabajos, materiales y control final que ya se hayan completado.</strong></p></div><div class="zg-type-switch-actions"><button type="button" class="zg-type-switch-cancel" id="zgTypeSwitchCancel">Cancelar</button><button type="button" class="zg-type-switch-confirm" id="zgTypeSwitchConfirm">Sí, cambiar equipo</button></div></div>';
    document.body.appendChild(modal);
    byId('zgTypeSwitchCancel').addEventListener('click',()=>{pendingType='';modal.classList.remove('show');if(typeSelect)typeSelect.value=previousType;});
    byId('zgTypeSwitchConfirm').addEventListener('click',()=>{const next=pendingType;pendingType='';modal.classList.remove('show');clearTypeData();if(typeSelect)typeSelect.value=next;previousType=next;applyMode();try{if(typeof toast==='function')toast('Tipo de equipo cambiado. Se limpiaron los datos incompatibles.');}catch(e){}});
    modal.addEventListener('click',e=>{if(e.target===modal)byId('zgTypeSwitchCancel').click();});
    return modal;
  }
  function requestTypeChange(next){
    if(!next){if(typeSelect)typeSelect.value=previousType;return;}
    if(previousType&&next!==previousType&&hasTypeData()){
      pendingType=next;if(typeSelect)typeSelect.value=previousType;
      const modal=ensureSwitchModal(),txt=byId('zgTypeSwitchText');
      if(txt)txt.textContent='Estás por cambiar de '+labelType(previousType)+' a '+labelType(next)+'. ¿Deseas continuar?';
      modal.classList.add('show');return;
    }
    previousType=next;applyMode();
  }

  function validateConfigV10(){
    ['zgTipoEquipo','zgModalidadComercial','zgTipoInstalacion','zgTamanoContenedor','zgMaquinaPreliminarObjetivo'].forEach(clearError);
    const type=equipmentType();if(!type)return markError('zgTipoEquipo','Selecciona primero si se atenderá una máquina reefer o un generador.');
    if(!clean(byId('zgModalidadComercial')?.value))return markError('zgModalidadComercial','Selecciona si el servicio corresponde a alquiler o venta.');
    if(type==='Reefer'){
      const inst=clean(byId('zgTipoInstalacion')?.value);if(!inst)return markError('zgTipoInstalacion','Selecciona el tipo de instalación de la máquina reefer.');
      if(!clean(byId('zgTamanoContenedor')?.value))return markError('zgTamanoContenedor','Selecciona el tamaño del contenedor o indica que no aplica.');
      if(inst==='Túnel'){
        for(let i=1;i<=5;i++)for(const [k,l] of [['Brand','marca'],['Controller','controlador'],['Serial','número de serie']]){const id='zgMachine'+k+i;if(!clean(byId(id)?.value))return markError(id,'Completa '+l+' de la máquina '+i+'.');}
        if(!clean(byId('zgMaquinaPreliminarObjetivo')?.value))return markError('zgMaquinaPreliminarObjetivo','Selecciona la máquina de referencia para la preliminar.');
      }
    }
    return true;
  }
  window.zgValidarConfiguracionServicio=validateConfigV10;

  function validatePreV10(){
    if(!validateConfigV10())return false;
    try{if(typeof zgroupEstadoInicialCompuesto==='function')zgroupEstadoInicialCompuesto();}catch(e){}
    const type=equipmentType(),eqLabel=type==='Genset'?'N° de generador / equipo':'Contenedor / equipo';
    if(typeof validarTextoCampo==='function'){
      if(!validarTextoCampo('equipoNo',eqLabel,3,60,true,/^[A-Za-z0-9\-_.\/]+$/))return false;
      if(!validarTextoCampo('serialUnidad','Serial unidad',3,80,true,/^[A-Za-z0-9\-_.\/]+$/))return false;
      if(!validarTextoCampo('controladorEquipo','Controlador',2,60,false))return false;
    }
    const brand=clean(byId('marcaEquipo')?.value).toUpperCase();if(!brand)return markError('marcaEquipo','Selecciona la marca del equipo.');
    if(type==='Genset'&&brand!=='THERMO KING')return markError('marcaEquipo','Para generadores selecciona la marca THERMO KING.');
    if(type==='Genset'&&!GENERATOR_CONTROLLERS.includes(clean(byId('controladorEquipo')?.value).toUpperCase()))return markError('controladorEquipo','Selecciona SG-3000 o SG-5000 como controlador del generador.');
    try{if(typeof validarEstadoInicialTriple==='function'&&!validarEstadoInicialTriple())return false;}catch(e){}
    if(type==='Reefer'){
      if(typeof validarTemp==='function'&&!(validarTemp('setPoint',-35,30,'Set point')&&validarTemp('temperaturaAmbiente',-10,60,'Temperatura ambiente')&&validarTemp('retornoAire',-40,60,'Retorno de aire')&&validarTemp('suministroAire',-50,60,'Suministro de aire')))return false;
    }else{
      const req=[['gensetHorometroInicial','Registra el horómetro inicial del generador.'],['gensetVoltajeBateriaInicial','Registra el voltaje inicial de la batería.'],['gensetNivelCombustibleInicial','Selecciona el nivel inicial de combustible.'],['gensetNivelAceiteInicial','Selecciona el nivel inicial de aceite.'],['gensetRefrigeranteMotorInicial','Selecciona el estado del refrigerante del motor.'],['gensetArranqueInicial','Selecciona el resultado de la prueba de arranque inicial.']];
      for(const [id,msg] of req)if(!clean(byId(id)?.value))return markError(id,msg);
    }
    if(typeof validarVoltajeCampo==='function'&&!(validarVoltajeCampo('voltajeL1L2','Voltaje L1-L2')&&validarVoltajeCampo('voltajeL2L3','Voltaje L2-L3')&&validarVoltajeCampo('voltajeL1L3','Voltaje L1-L3')))return false;
    if(typeof validarTextoCampo==='function'&&!validarTextoCampo('observacionInicial','Observación inicial',3,600,false,/^[\s\S]+$/))return false;
    return true;
  }

  function validateMaintenance(){
    const req=clean(byId('zgRequiereOtroMantenimiento')?.value);if(!req)return markError('zgRequiereOtroMantenimiento','Indica si el equipo requiere otro mantenimiento.');
    if(req==='Sí'){
      if(!clean(byId('zgTipoOtroMantenimiento')?.value))return markError('zgTipoOtroMantenimiento','Selecciona el tipo de mantenimiento requerido.');
      if(clean(byId('zgMotivoOtroMantenimiento')?.value).length<10)return markError('zgMotivoOtroMantenimiento','Explica la razón del mantenimiento con al menos 10 caracteres.');
    }
    return true;
  }
  function validateFinalV10(){
    if(equipmentType()==='Genset'){
      const req=[['gensetEstadoFinal','Selecciona el estado final del generador.'],['gensetHorometroFinal','Registra el horómetro final.'],['gensetArranqueFinal','Selecciona el resultado de la prueba de arranque.'],['gensetVoltajeBateriaFinal','Registra el voltaje final de la batería.'],['gensetVoltajeSalidaL1L2','Registra el voltaje de salida L1-L2.'],['gensetVoltajeSalidaL2L3','Registra el voltaje de salida L2-L3.'],['gensetVoltajeSalidaL1L3','Registra el voltaje de salida L1-L3.'],['gensetTemperaturaMotorFinal','Registra la temperatura final del motor.'],['gensetNivelCombustibleFinal','Selecciona el nivel final de combustible.']];
      for(const [id,msg] of req)if(!clean(byId(id)?.value))return markError(id,msg);
      return validateMaintenance();
    }
    if(!clean(byId('estadoFinalEquipo')?.value))return markError('estadoFinalEquipo','Selecciona el estado final de la máquina reefer.');
    if(typeof validarTemp==='function'&&!(validarTemp('setPointFinal',-35,30,'Set point final')&&validarTemp('tempAmbienteFinal',-10,60,'Temperatura ambiente final')&&validarTemp('retornoFinal',-40,60,'Retorno de aire final')&&validarTemp('suministroFinal',-50,60,'Suministro de aire final')))return false;
    if(typeof validarVoltajeCampo==='function'&&!(validarVoltajeCampo('voltajeFinalL1L2','Voltaje final L1-L2')&&validarVoltajeCampo('voltajeFinalL2L3','Voltaje final L2-L3')&&validarVoltajeCampo('voltajeFinalL1L3','Voltaje final L1-L3')))return false;
    return validateMaintenance();
  }
  function dataFinalV10(){
    const m={requiereOtroMantenimiento:clean(byId('zgRequiereOtroMantenimiento')?.value),tipoOtroMantenimiento:clean(byId('zgTipoOtroMantenimiento')?.value),motivoOtroMantenimiento:clean(byId('zgMotivoOtroMantenimiento')?.value),tipoEquipo:equipmentType()};
    if(equipmentType()==='Genset')return Object.assign(m,{gensetEstadoFinal:clean(byId('gensetEstadoFinal')?.value),gensetHorometroFinal:clean(byId('gensetHorometroFinal')?.value),gensetArranqueFinal:clean(byId('gensetArranqueFinal')?.value),gensetPruebaCargaFinal:clean(byId('gensetPruebaCargaFinal')?.value),gensetVoltajeBateriaFinal:clean(byId('gensetVoltajeBateriaFinal')?.value),gensetFrecuenciaFinal:clean(byId('gensetFrecuenciaFinal')?.value),gensetVoltajeSalidaL1L2:clean(byId('gensetVoltajeSalidaL1L2')?.value),gensetVoltajeSalidaL2L3:clean(byId('gensetVoltajeSalidaL2L3')?.value),gensetVoltajeSalidaL1L3:clean(byId('gensetVoltajeSalidaL1L3')?.value),gensetPresionAceiteFinal:clean(byId('gensetPresionAceiteFinal')?.value),gensetTemperaturaMotorFinal:clean(byId('gensetTemperaturaMotorFinal')?.value),gensetNivelCombustibleFinal:clean(byId('gensetNivelCombustibleFinal')?.value)});
    return Object.assign(m,{estadoFinalEquipo:clean(byId('estadoFinalEquipo')?.value),setPointFinal:clean(byId('setPointFinal')?.value),tempAmbienteFinal:clean(byId('tempAmbienteFinal')?.value),presionAltaFinal:clean(byId('presionAltaFinal')?.value),presionBajaFinal:clean(byId('presionBajaFinal')?.value),retornoFinal:clean(byId('retornoFinal')?.value),suministroFinal:clean(byId('suministroFinal')?.value),voltajeFinalL1L2:clean(byId('voltajeFinalL1L2')?.value),voltajeFinalL2L3:clean(byId('voltajeFinalL2L3')?.value),voltajeFinalL1L3:clean(byId('voltajeFinalL1L3')?.value)});
  }

  function replaceListeners(){
    const old=byId('zgTipoEquipo');if(!old)return;
    const clone=old.cloneNode(true);clone.value=old.value;clone.dataset.zgV8='1';old.replaceWith(clone);typeSelect=clone;
    previousType=clean(clone.value);
    clone.addEventListener('change',()=>requestTypeChange(clean(clone.value)));
    const brandOld=byId('marcaEquipo');if(brandOld){const b=brandOld.cloneNode(true);b.value=brandOld.value;b.dataset.zgV8='1';brandOld.replaceWith(b);b.addEventListener('change',()=>{actualizarControladorV10(true);clearError('marcaEquipo');clearError('controladorEquipo');});}
    const inst=byId('zgTipoInstalacion');if(inst&&!inst.dataset.zgV10){inst.dataset.zgV10='1';inst.addEventListener('change',()=>applyMode());}
    const clearBtn=byId('clearBtn');if(clearBtn&&!clearBtn.dataset.zgTypeV10){clearBtn.dataset.zgTypeV10='1';clearBtn.addEventListener('click',()=>setTimeout(()=>{previousType=equipmentType();applyMode();},120));}
  }
  function installOverrides(){
    window.zgValidarConfiguracionServicio=validateConfigV10;
    window.validarInspeccionPreliminar=validatePreV10;try{validarInspeccionPreliminar=validatePreV10}catch(e){}
    window.validarControlFinal=validateFinalV10;try{validarControlFinal=validateFinalV10}catch(e){}
    window.datosControlFinal=dataFinalV10;try{datosControlFinal=dataFinalV10}catch(e){}
  }
  function init(){
    replaceListeners();installOverrides();ensureSwitchModal();applyMode();
    [250,800,1600,3000].forEach(ms=>setTimeout(()=>{installOverrides();applyMode();},ms));
  }
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init,{once:true});else init();
  window.addEventListener('load',()=>{installOverrides();applyMode();},{once:true});
})();

(function(){
  'use strict';
  const byId=id=>document.getElementById(id);
  const clean=v=>String(v==null?'':v).replace(/\s+/g,' ').trim();
  const norm=v=>clean(v).toUpperCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'');
  const isGenset=()=>clean(byId('zgTipoEquipo')?.value)==='Genset';
  const escape=v=>String(v==null?'':v).replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));

  // Catálogo de materiales exclusivo para generadores.
  if(window.ZG_CATALOGOS_POR_CONTROLADOR){
    window.ZG_CATALOGOS_POR_CONTROLADOR['GENSET SG-3000']=(Array.isArray(REPUESTOS_GENSET_CATALOGO)?REPUESTOS_GENSET_CATALOGO:[]).filter(r=>norm(r.controlador)==='SG-3000');
    window.ZG_CATALOGOS_POR_CONTROLADOR['GENSET SG-5000']=(Array.isArray(REPUESTOS_GENSET_CATALOGO)?REPUESTOS_GENSET_CATALOGO:[]).filter(r=>norm(r.controlador)==='SG-5000');
  }

  function generatorRows(q){
    const query=norm(q);
    return (Array.isArray(GENERADORES_CATALOGO)?GENERADORES_CATALOGO:[]).filter(g=>{
      const hay=[g.numero,g.serial_unidad,g.marca_equipo,g.controlador].some(v=>norm(v).includes(query));
      return !query||hay;
    }).sort((a,b)=>String(a.numero||'').localeCompare(String(b.numero||''),'es',{numeric:true,sensitivity:'base'}));
  }
  function reeferContainers(q){
    const query=norm(q);
    const genNums=new Set((Array.isArray(GENERADORES_CATALOGO)?GENERADORES_CATALOGO:[]).map(g=>norm(g.numero)));
    return (Array.isArray(CONTENEDORES_CATALOGO)?CONTENEDORES_CATALOGO:[]).filter(c=>{
      if(genNums.has(norm(c.numero)))return false;
      return !query||[c.numero,c.serial_unidad,c.marca_equipo].some(v=>norm(v).includes(query));
    }).sort((a,b)=>String(a.numero||'').localeCompare(String(b.numero||''),'es',{numeric:true,sensitivity:'base'}));
  }
  function reeferMachines(q){
    const query=norm(q);
    const genSerials=new Set((Array.isArray(GENERADORES_CATALOGO)?GENERADORES_CATALOGO:[]).map(g=>norm(g.serial_unidad)).filter(Boolean));
    return (Array.isArray(MAQUINAS_CATALOGO)?MAQUINAS_CATALOGO:[]).filter(m=>{
      if(genSerials.has(norm(m.serial_unidad)))return false;
      const joined=norm((m.marca_equipo||'')+' '+(m.controlador||''));
      if(joined.includes('GENSET')||joined.includes('SG-3000')||joined.includes('SG 3000')||joined.includes('SG-5000')||joined.includes('SG 5000'))return false;
      return !query||[m.serial_unidad,m.marca_equipo,m.controlador,m.refrigerante].some(v=>norm(v).includes(query));
    }).sort((a,b)=>String(a.serial_unidad||'').localeCompare(String(b.serial_unidad||''),'es',{numeric:true,sensitivity:'base'}));
  }

  window.contenedoresFiltrados=function(q){return isGenset()?generatorRows(q):reeferContainers(q)};
  window.maquinasFiltradas=function(q){return isGenset()?generatorRows(q):reeferMachines(q)};

  window.mostrarContenedores=function(){
    const input=byId('equipoNo'),q=input?.value||'';
    const rows=window.contenedoresFiltrados(q);
    const items=rows.map(r=>isGenset()?{
      raw:r,main:r.numero||'',sub:['Generador registrado en panel',r.serial_unidad?'Serial: '+r.serial_unidad:'',r.controlador?'Ctrl: '+r.controlador:''].filter(Boolean).join(' · ')
    }:{raw:r,main:r.numero||'',sub:'Contenedor registrado en panel'});
    renderSmartMenu('contenedorSuggest',items,r=>{
      setVal('equipoNo',r.numero||'');
      if(isGenset()){
        if(r.serial_unidad)setVal('serialUnidad',r.serial_unidad);
        setVal('marcaEquipo','THERMO KING');
        if(r.controlador)setVal('controladorEquipo',String(r.controlador).replace(/^ZG-/i,'SG-'));
        try{if(typeof actualizarOpcionesControlador==='function')actualizarOpcionesControlador(false)}catch(e){}
        setCatalogHint('contenedorHint','Generador seleccionado desde el catálogo exclusivo de gensets.','ok');
      }else setCatalogHint('contenedorHint','Contenedor seleccionado desde el panel.','ok');
      clearFieldError('equipoNo');
    });
    if(items.length)setCatalogHint('contenedorHint',isGenset()?'Selecciona un generador creado en el apartado Generadores del panel.':'Selecciona un contenedor creado en el panel.',q?'ok':'');
    else setCatalogHint('contenedorHint',isGenset()?'No hay generadores coincidentes. Créalo en el apartado Generadores del panel.':'No hay contenedores coincidentes.','warn');
  };

  window.mostrarMaquinas=function(){
    const input=byId('serialUnidad'),q=input?.value||'';
    const rows=window.maquinasFiltradas(q);
    const items=rows.map(r=>isGenset()?{
      raw:r,main:r.serial_unidad||r.numero||'',sub:['Generador: '+(r.numero||'-'),'Marca: THERMO KING',r.controlador?'Ctrl: '+r.controlador:''].filter(Boolean).join(' · ')
    }:{raw:r,main:r.serial_unidad||'',sub:[r.marca_equipo?'Marca: '+r.marca_equipo:'',r.controlador?'Ctrl: '+r.controlador:'',r.refrigerante?'Ref: '+r.refrigerante:''].filter(Boolean).join(' · ')||'Máquina reefer registrada'});
    renderSmartMenu('maquinaSuggest',items,r=>{
      setVal('serialUnidad',r.serial_unidad||'');
      if(isGenset()){
        if(r.numero)setVal('equipoNo',r.numero);
        setVal('marcaEquipo','THERMO KING');
        if(r.controlador)setVal('controladorEquipo',String(r.controlador).replace(/^ZG-/i,'SG-'));
        try{if(typeof actualizarOpcionesControlador==='function')actualizarOpcionesControlador(false)}catch(e){}
        setCatalogHint('maquinaHint','Serial seleccionado desde el catálogo exclusivo de generadores.','ok');
      }else{
        if(r.marca_equipo)setVal('marcaEquipo',r.marca_equipo);
        if(r.controlador)setVal('controladorEquipo',r.controlador);
        if(r.refrigerante)setVal('refrigerante',r.refrigerante);
        setCatalogHint('maquinaHint','Máquina reefer seleccionada desde el panel.','ok');
      }
      clearFieldError('serialUnidad');
    });
    if(items.length)setCatalogHint('maquinaHint',isGenset()?'Selecciona el serial del generador.':'Selecciona una máquina reefer creada en el panel.',q?'ok':'');
    else setCatalogHint('maquinaHint',isGenset()?'No hay seriales de generadores coincidentes.':'No hay máquinas reefer coincidentes.','warn');
  };

  const GENSET_BANK={
    actividades:[
      'Inspección visual del motor','Verificación de nivel de aceite','Verificación de refrigerante del motor','Revisión de fugas de aceite','Revisión de fugas de combustible','Revisión de fugas de refrigerante','Revisión del filtro de aire','Revisión del filtro de combustible','Revisión del filtro de aceite','Revisión de batería y bornes','Medición de voltaje de batería','Revisión del motor de arranque','Revisión del alternador','Revisión de fajas y poleas','Revisión de radiador','Revisión de sensores del motor','Revisión de relés y fusibles','Revisión de cableado y conexiones','Revisión del controlador SG','Prueba de arranque','Prueba sin carga','Prueba bajo carga','Medición de voltajes de salida','Medición de frecuencia','Medición de presión de aceite','Medición de temperatura del motor','Limpieza técnica del generador','Registro fotográfico'
    ],
    hallazgos:[
      'Sin novedad','Aceite de motor bajo','Aceite de motor degradado','Fuga de aceite','Combustible bajo','Fuga de combustible','Filtro de combustible saturado','Filtro de aceite saturado','Filtro de aire sucio','Refrigerante del motor bajo','Fuga de refrigerante','Radiador obstruido','Batería descargada','Bornes sulfatados','Motor de arranque con falla','Alternador sin generación','Voltaje de salida fuera de rango','Frecuencia fuera de rango','Presión de aceite baja','Temperatura de motor elevada','Faja desgastada o floja','Sensor con falla','Relé o fusible dañado','Cableado o conexión floja','Alarma activa en controlador','Solenoide de combustible con falla','Ruido o vibración anormal','Generador no arranca','Generador se apaga bajo carga'
    ],
    acciones:[
      'Se cambió aceite de motor','Se reemplazó filtro de aceite','Se reemplazó filtro de combustible','Se reemplazó filtro de aire','Se completó refrigerante del motor','Se corrigió fuga de aceite','Se corrigió fuga de combustible','Se corrigió fuga de refrigerante','Se limpiaron bornes de batería','Se ajustaron conexiones de batería','Se reemplazó batería','Se ajustó o reemplazó faja','Se limpió radiador','Se reparó cableado','Se ajustaron terminales eléctricos','Se reemplazó sensor','Se reemplazó relé o fusible','Se intervino motor de arranque','Se intervino alternador','Se revisó controlador SG','Se borraron y verificaron alarmas','Se realizó prueba de arranque','Se realizó prueba sin carga','Se realizó prueba bajo carga','Se verificaron voltajes y frecuencia','Se verificó presión de aceite','Se verificó temperatura del motor','Se dejó operativo','Se deja pendiente por repuesto','Se tomó registro fotográfico'
    ]
  };
  GENSET_BANK.estados=['Operativo','Operativo con observación','Pendiente por repuesto','Requiere seguimiento','No operativo'];
  const GENSET_PRESETS={
    'Mantenimiento preventivo SG':{
      actividades:['Inspección visual del motor','Verificación de nivel de aceite','Verificación de refrigerante del motor','Revisión del filtro de aire','Revisión del filtro de combustible','Revisión del filtro de aceite','Revisión de batería y bornes','Revisión de fajas y poleas','Revisión de radiador','Revisión de cableado y conexiones','Prueba de arranque','Prueba sin carga','Medición de voltajes de salida','Medición de frecuencia','Medición de presión de aceite','Registro fotográfico'],
      hallazgos:['Sin novedad'],
      acciones:['Se realizó prueba de arranque','Se realizó prueba sin carga','Se verificaron voltajes y frecuencia','Se verificó presión de aceite','Se dejó operativo','Se tomó registro fotográfico'],recomendaciones:[],estado:'Operativo'
    },
    'Mantenimiento correctivo SG':{
      actividades:['Inspección visual del motor','Revisión de batería y bornes','Revisión del motor de arranque','Revisión del alternador','Revisión de sensores del motor','Revisión de relés y fusibles','Revisión de cableado y conexiones','Revisión del controlador SG','Prueba de arranque','Prueba bajo carga','Medición de voltajes de salida','Medición de frecuencia','Medición de presión de aceite','Registro fotográfico'],
      hallazgos:['Alarma activa en controlador'],
      acciones:['Se reparó cableado','Se ajustaron terminales eléctricos','Se borraron y verificaron alarmas','Se realizó prueba de arranque','Se realizó prueba bajo carga','Se verificaron voltajes y frecuencia','Se tomó registro fotográfico'],recomendaciones:[],estado:'Operativo con observación'
    }
  };

  const oldPresetSuggested=window.presetSugeridoPorTrabajo||presetSugeridoPorTrabajo;
  window.presetSugeridoPorTrabajo=presetSugeridoPorTrabajo=function(nombre){
    if(isGenset())return norm(nombre).includes('CORRECTIVO')?'Mantenimiento correctivo SG':'Mantenimiento preventivo SG';
    return oldPresetSuggested(nombre);
  };
  const oldApplySilent=window.aplicarPresetSilencioso||aplicarPresetSilencioso;
  window.aplicarPresetSilencioso=aplicarPresetSilencioso=function(s,nombre){
    if(!isGenset())return oldApplySilent(s,nombre);
    const key=nombre&&GENSET_PRESETS[nombre]?nombre:(norm(s.nombre).includes('CORRECTIVO')?'Mantenimiento correctivo SG':'Mantenimiento preventivo SG');
    const p=GENSET_PRESETS[key];
    s.auto=emptyAuto();s.auto.plantilla=key;
    ['actividades','hallazgos','acciones','recomendaciones'].forEach(k=>s.auto[k]=Array.isArray(p[k])?p[k].slice():[]);
    s.auto.estado=p.estado||'';
    s.detalle=generarTextoAutomaticoTrabajo(s,key);
  };
  window.buildQuickAssistant=buildQuickAssistant=function(s){
    if(!s.auto)s.auto=emptyAuto();
    const genset=isGenset();
    const bank=genset?GENSET_BANK:QUICK_BANK;
    const presetsMap=genset?GENSET_PRESETS:PRESETS;
    const tiene=['actividades','hallazgos','acciones','recomendaciones'].some(k=>Array.isArray(s.auto[k])&&s.auto[k].length);
    if(!tiene&&!(s.auto&&s.auto.limpiado))aplicarPresetSilencioso(s,presetSugeridoPorTrabajo(s.nombre||''));
    const box=document.createElement('div');box.className='quick-assistant';
    const head=document.createElement('div');head.className='quick-head';
    const htxt=document.createElement('div');htxt.innerHTML='<strong>'+(genset?'Sugerencias para generador':'Sugerencias del servicio')+'</strong><small>'+(genset?'Opciones reales de mantenimiento de motor y generación eléctrica.':'Opciones sugeridas. El técnico puede desmarcar o ajustar lo que no corresponda.')+'</small>';
    const actions=document.createElement('div');actions.className='quick-actions';
    const reset=document.createElement('button');reset.className='quick-reset';reset.type='button';reset.textContent='Limpiar opciones';reset.onclick=()=>{s.auto=emptyAuto();s.auto.limpiado=true;s.detalle='';renderPanels();toast('Opciones limpiadas. Puedes seleccionar desde cero.');};
    actions.appendChild(reset);head.append(htxt,actions);box.appendChild(head);
    const templates=document.createElement('div');templates.className='template-row';
    Object.keys(presetsMap).forEach(name=>{const b=document.createElement('button');b.type='button';b.className='template-btn'+((s.auto&&s.auto.plantilla===name)?' on':'');b.textContent=name;b.onclick=()=>applyPreset(s,name,presetsMap[name]);templates.appendChild(b);});
    box.appendChild(templates);
    const grid=document.createElement('div');grid.className='quick-grid';
    grid.appendChild(chipGroup(s,'actividades','Actividades realizadas',bank.actividades));
    grid.appendChild(chipGroup(s,'hallazgos','Observaciones',bank.hallazgos,'warn'));
    grid.appendChild(chipGroup(s,'acciones','Acciones ejecutadas',bank.acciones,'ok'));
    box.appendChild(grid);
    if(!genset&&(s.id==='revision_tecnica'||norm(s.nombre).includes('REVISION')||norm(s.nombre).includes('PTI')))box.appendChild(buildPtiBox(s));
    return box;
  };

  function refreshOnType(){
    setTimeout(()=>{
      if(isGenset()){
        try{Object.values(state.selected||{}).forEach(s=>{if(/^genset_/.test(s.id)&&(!s.auto||!String(s.auto.plantilla||'').includes(' SG')))aplicarPresetSilencioso(s,presetSugeridoPorTrabajo(s.nombre));});}catch(e){}
      }
      try{if(typeof renderPanels==='function')renderPanels()}catch(e){}
    },80);
  }
  document.addEventListener('change',e=>{if(e.target&&e.target.id==='zgTipoEquipo')refreshOnType();},true);
  window.addEventListener('load',refreshOnType,{once:true});
})();

(function(){
  function aplicar(){
    document.querySelectorAll('textarea[id^="campo_genset_mantenimiento_preventivo_detalle_tecnico"]').forEach(function(x){
      x.placeholder='Describe una precisión relevante del mantenimiento, la condición encontrada o el resultado obtenido.';
    });
    document.querySelectorAll('textarea[id^="campo_genset_mantenimiento_correctivo_detalle_tecnico"]').forEach(function(x){
      x.placeholder='Describe la falla atendida, la corrección aplicada y la condición final del generador.';
    });
  }
  const mo=new MutationObserver(aplicar);
  document.addEventListener('DOMContentLoaded',function(){aplicar();try{mo.observe(document.body,{childList:true,subtree:true});}catch(e){}});
  window.addEventListener('load',aplicar);
})();

(function(){
  function clean(v){return String(v==null?'':v).replace(/\s+/g,' ').trim();}
  function norm(v){return clean(v).toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'');}
  function unique(arr){
    const out=[], seen=new Set();
    (Array.isArray(arr)?arr:[]).forEach(function(v){
      v=clean(v); if(!v) return;
      const k=norm(v); if(seen.has(k)) return;
      seen.add(k); out.push(v);
    });
    return out;
  }
  function markChanged(){try{if(window.zgroupMarcarCambio)window.zgroupMarcarCambio();}catch(e){}}
  function isGenset(){
    const v=norm(document.getElementById('zgTipoEquipo')?.value||'');
    return v.includes('genset')||v.includes('generador');
  }

  function tipoEquipoActual(){return isGenset()?'genset':'reefer';}
  function claveTrabajo(s){
    const raw=clean((s&&s.id)||(s&&s.nombre)||'trabajo_general').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'');
    return raw.replace(/[^a-z0-9]+/g,'_').replace(/^_+|_+$/g,'')||'trabajo_general';
  }
  function opcionesPersonalizadas(key,s){
    try{
      const tipo=tipoEquipoActual();
      const trabajo=claveTrabajo(s);
      const tipoBanco=OPCIONES_TECNICAS_POR_TRABAJO&&OPCIONES_TECNICAS_POR_TRABAJO[tipo];
      const grupo=tipoBanco&&tipoBanco[trabajo];
      return unique(grupo&&Array.isArray(grupo[key])?grupo[key]:[]);
    }catch(e){return [];}
  }
  function asegurarBancoTrabajo(tipo,trabajo){
    const banco=typeof OPCIONES_TECNICAS_POR_TRABAJO!=='undefined'?OPCIONES_TECNICAS_POR_TRABAJO:null;
    if(!banco)return null;
    if(!banco[tipo])banco[tipo]={};
    if(!banco[tipo][trabajo])banco[tipo][trabajo]={actividades:[],hallazgos:[]};
    return banco[tipo][trabajo];
  }
  window.zgOpcionesTecnicasTrabajo=function(key,s){return opcionesPersonalizadas(key,s);};
  async function guardarOpcionPersonalizada(valor,key,s){
    valor=clean(valor);
    if(!valor||!['actividades','hallazgos'].includes(key))return;
    const tipo=tipoEquipoActual();
    const trabajo=claveTrabajo(s);
    const grupo=asegurarBancoTrabajo(tipo,trabajo);
    if(grupo){
      if(!Array.isArray(grupo[key]))grupo[key]=[];
      grupo[key]=unique(grupo[key].concat(valor));
    }

    try{
      const fd=new FormData();
      fd.append('tipo_equipo',tipo);
      fd.append('categoria',key);
      fd.append('texto',valor);
      fd.append('trabajo_clave',trabajo);
      fd.append('trabajo_nombre',clean((s&&s.nombre)||''));
      fd.append('preinspeccion_id',String(document.getElementById('preinspeccionId')?.value||window.PREINSPECCION?.id||window.PREINSPECCION?.pre_id||''));
      fd.append('token_continuacion',String(document.getElementById('tokenContinuacion')?.value||window.TOKEN_CONTINUACION||''));
      fd.append('tecnico_id',String((window.state&&window.state.tecnicoId)||''));
      const r=await fetch('registrar_opcion_tecnica.php',{method:'POST',body:fd,credentials:'same-origin',headers:{'X-Requested-With':'XMLHttpRequest'}});
      let d={};
      try{d=await r.json();}catch(e){throw new Error('El servidor no devolvió una respuesta válida.');}
      if(!r.ok||!d.ok)throw new Error((d&&d.error)||'No se pudo guardar la opción');
      if(grupo)grupo[key]=unique(grupo[key].concat(d.texto||valor));
      try{if(typeof toast==='function')toast('Opción guardada para este tipo de trabajo.');}catch(e){}
    }catch(err){
      console.warn('No se pudo registrar la opción técnica personalizada:',err);
      try{if(typeof toast==='function')toast('La opción quedó en este informe, pero no se guardó para futuros servicios: '+(err.message||err));}catch(e){}
    }
  }


  const GEN_ACTIVIDADES=[
    'Se realizó inspección visual general del generador',
    'Se realizó limpieza exterior del generador',
    'Se limpió el compartimiento del motor',
    'Se limpió el radiador',
    'Se limpió la zona de admisión de aire',
    'Se limpió el tanque de combustible',
    'Se drenó agua del sistema de combustible',
    'Se purgó el sistema de combustible',
    'Se cambió aceite de motor',
    'Se completó nivel de aceite de motor',
    'Se reemplazó filtro de aceite',
    'Se reemplazó filtro de combustible',
    'Se reemplazó filtro de aire',
    'Se completó refrigerante del motor',
    'Se reemplazó refrigerante del motor',
    'Se corrigió fuga de aceite',
    'Se corrigió fuga de combustible',
    'Se corrigió fuga de refrigerante',
    'Se ajustaron abrazaderas y mangueras',
    'Se reemplazó manguera de combustible',
    'Se reemplazó manguera de refrigerante',
    'Se ajustó la faja del motor',
    'Se reemplazó la faja del motor',
    'Se reemplazó polea de bomba de agua',
    'Se reemplazó bomba de agua',
    'Se limpió el sistema de ventilación',
    'Se limpiaron bornes de batería',
    'Se ajustaron conexiones de batería',
    'Se realizó carga de batería',
    'Se reemplazó batería',
    'Se reparó cableado eléctrico',
    'Se ajustaron terminales eléctricos',
    'Se reemplazó terminal eléctrico',
    'Se reemplazó fusible',
    'Se reemplazó relé',
    'Se reemplazó regulador de voltaje',
    'Se reemplazó sensor de RPM',
    'Se reemplazó sensor de temperatura del motor',
    'Se reemplazó sensor de presión de aceite',
    'Se reemplazó sensor de nivel de aceite',
    'Se reemplazó sensor de nivel de refrigerante',
    'Se reemplazó sensor de combustible',
    'Se reemplazó solenoide de combustible',
    'Se intervino el motor de arranque',
    'Se reemplazó el motor de arranque',
    'Se intervino el alternador',
    'Se reemplazó el alternador',
    'Se reparó el arnés principal',
    'Se reemplazó el arnés principal',
    'Se reparó la caja de control',
    'Se reemplazó el controlador SG',
    'Se reemplazó el teclado del controlador SG',
    'Se configuraron parámetros del controlador SG',
    'Se descargaron datos del controlador SG',
    'Se borraron alarmas del controlador SG',
    'Se realizó prueba de arranque',
    'Se realizó prueba sin carga',
    'Se realizó prueba bajo carga',
    'Se midió voltaje de batería',
    'Se midieron voltajes de salida',
    'Se midió frecuencia de salida',
    'Se midió presión de aceite',
    'Se midió temperatura del motor',
    'Se ajustó el voltaje de salida',
    'Se instaló receptáculo de salida 480 V',
    'Se reemplazó enchufe de salida 32 A',
    'Se reparó la conexión de salida eléctrica',
    'Se realizó limpieza técnica del generador',
    'Se realizó lubricación de componentes móviles',
    'Se aplicó tratamiento anticorrosivo',
    'Se realizó ajuste general de pernos y soportes',
    'Se reparó la tapa o estructura del generador',
    'Se dejó el generador operativo',
    'Se dejó el generador pendiente por repuesto',
    'Se tomó registro fotográfico'
  ];

  const REEFER_ACTIVIDADES=[
    'Se realizó inspección visual general de la unidad reefer',
    'Se realizó limpieza exterior de la unidad reefer',
    'Se limpió el evaporador',
    'Se limpió el condensador',
    'Se limpió la bandeja de drenaje',
    'Se limpiaron las líneas de drenaje',
    'Se destapó el drenaje del evaporador',
    'Se realizó deshielo manual del equipo',
    'Se retiró hielo acumulado',
    'Se limpió el sensor de retorno de aire',
    'Se limpió el sensor de suministro de aire',
    'Se reemplazó el sensor de retorno de aire',
    'Se reemplazó el sensor de suministro de aire',
    'Se reemplazó el sensor de temperatura ambiente',
    'Se ajustaron conexiones del sensor',
    'Se reparó cableado del sistema de control',
    'Se reemplazó cableado dañado',
    'Se ajustaron terminales eléctricos',
    'Se reemplazó fusible',
    'Se reemplazó relé',
    'Se reemplazó contactor',
    'Se reemplazó capacitor',
    'Se reemplazó protector térmico',
    'Se reemplazó teclado del controlador',
    'Se reemplazó pantalla del controlador',
    'Se reemplazó controlador de la unidad',
    'Se configuraron parámetros del controlador',
    'Se descargaron datos del controlador',
    'Se borraron alarmas del controlador',
    'Se ajustó el set point',
    'Se midió temperatura de retorno de aire',
    'Se midió temperatura de suministro de aire',
    'Se midió temperatura ambiente',
    'Se midió presión de alta',
    'Se midió presión de baja',
    'Se midieron voltajes de alimentación',
    'Se realizó prueba de aislamiento eléctrico',
    'Se realizó prueba de continuidad eléctrica',
    'Se realizó prueba de fugas de refrigerante',
    'Se corrigió fuga de refrigerante',
    'Se realizó carga de refrigerante',
    'Se recuperó refrigerante del sistema',
    'Se realizó vacío al sistema de refrigeración',
    'Se reemplazó filtro deshidratador',
    'Se reemplazó válvula de expansión',
    'Se reemplazó válvula solenoide',
    'Se reemplazó válvula de servicio',
    'Se reemplazó presostato de alta',
    'Se reemplazó presostato de baja',
    'Se intervino el compresor',
    'Se reemplazó el compresor',
    'Se completó aceite del compresor',
    'Se reemplazó motor del ventilador del evaporador',
    'Se reemplazó motor del ventilador del condensador',
    'Se reemplazó ventilador del evaporador',
    'Se reemplazó ventilador del condensador',
    'Se limpió la hélice del ventilador',
    'Se ajustó la hélice del ventilador',
    'Se reemplazó resistencia de deshielo',
    'Se reemplazó resistencia de drenaje',
    'Se reparó el circuito de deshielo',
    'Se reemplazó calefactor de puerta',
    'Se reemplazó empaque de puerta',
    'Se ajustaron bisagras de puerta',
    'Se ajustó el cierre de puerta',
    'Se reparó aislamiento térmico',
    'Se sellaron puntos de ingreso de aire',
    'Se reparó conexión de alimentación eléctrica',
    'Se reemplazó enchufe de alimentación',
    'Se instaló luminaria interior',
    'Se reemplazó luminaria interior',
    'Se instaló interruptor de luminaria',
    'Se reparó cableado de luminarias',
    'Se realizó prueba de encendido de luminarias',
    'Se realizó limpieza técnica del equipo',
    'Se realizó prueba funcional del equipo',
    'Se realizó prueba de enfriamiento',
    'Se realizó prueba de calentamiento',
    'Se ejecutó prueba PTI',
    'Se ejecutó prueba Run Test',
    'Se realizó prueba de operación final',
    'Se realizó verificación visual del ventilador condensador',
    'Se realizó verificación de los ventiladores evaporadores',
    'Se inspeccionaron los contactos eléctricos',
    'Se inspeccionaron las conexiones eléctricas en busca de conexiones sueltas o dañadas',
    'Se verificaron los circuitos de protección',
    'Se descargaron los datos del controlador',
    'Se verificó la presencia de fugas',
    'Se verificó el nivel de refrigerante',
    'Se verificaron las presiones de descarga y succión',
    'Se verificó el filtro deshidratador',
    'Se verificó la válvula digital',
    'Se inspeccionó el equipo en busca de piezas dañadas o rotas',
    'Se revisaron y ajustaron los tornillos de montaje del motor condensador',
    'Se revisaron y ajustaron los tornillos de montaje de los motores evaporadores',
    'Se revisaron y ajustaron los tornillos de montaje del compresor',
    'Se realizó limpieza interna del reefer',
    'Se dejó la unidad reefer operativa',
    'Se dejó la unidad reefer pendiente por repuesto',
    'Se tomó registro fotográfico'
  ];

  const GEN_HALLAZGOS=[
    'Sin novedad','Aceite de motor bajo','Aceite de motor degradado','Fuga de aceite','Combustible bajo','Fuga de combustible','Filtro de combustible saturado','Filtro de aceite saturado','Filtro de aire sucio','Refrigerante del motor bajo','Fuga de refrigerante','Radiador obstruido','Batería descargada','Bornes sulfatados','Motor de arranque con falla','Alternador sin generación','Voltaje de salida fuera de rango','Frecuencia fuera de rango','Presión de aceite baja','Temperatura de motor elevada','Faja desgastada o floja','Sensor con falla','Relé o fusible dañado','Cableado o conexión floja','Alarma activa en controlador','Solenoide de combustible con falla','Ruido o vibración anormal','Generador no arranca','Generador se apaga bajo carga'
  ];

  const REEFER_HALLAZGOS_ADICIONALES=[
    'Se encontró el compresor escarchado; se requiere reemplazar la válvula de expansión',
    'Se requiere actualizar el software del controlador',
    'Se realizó el mantenimiento preventivo de la unidad reefer'
  ];

  const REEFER_PREVENTIVO_PRIORITARIO=[
    'Se realizó verificación visual del ventilador condensador',
    'Se realizó verificación de los ventiladores evaporadores',
    'Se inspeccionaron los contactos eléctricos',
    'Se inspeccionaron las conexiones eléctricas en busca de conexiones sueltas o dañadas',
    'Se verificaron los circuitos de protección',
    'Se descargaron los datos del controlador',
    'Se verificó la presencia de fugas',
    'Se verificó el nivel de refrigerante',
    'Se verificaron las presiones de descarga y succión',
    'Se verificó el filtro deshidratador',
    'Se verificó la válvula digital',
    'Se inspeccionó el equipo en busca de piezas dañadas o rotas',
    'Se revisaron y ajustaron los tornillos de montaje del motor condensador',
    'Se revisaron y ajustaron los tornillos de montaje de los motores evaporadores',
    'Se revisaron y ajustaron los tornillos de montaje del compresor',
    'Se realizó limpieza interna del reefer'
  ];

  function reeferActivities(){
    return unique(REEFER_ACTIVIDADES);
  }
  function actividadesParaTrabajo(s,genset){
    const propias=opcionesPersonalizadas('actividades',s);
    if(genset)return unique(GEN_ACTIVIDADES.concat(propias));
    const nombre=norm((s&&s.nombre)||'');
    // En mantenimiento preventivo se muestran primero las actividades entregadas
    // por el usuario; el resto del catálogo y la memoria histórica quedan detrás.
    if(nombre.includes('preventivo'))return unique(REEFER_PREVENTIVO_PRIORITARIO.concat(REEFER_ACTIVIDADES,propias));
    return unique(REEFER_ACTIVIDADES.concat(propias));
  }
  function reeferFindings(){
    try{return unique((QUICK_BANK.hallazgos||[]).concat(REEFER_HALLAZGOS_ADICIONALES));}
    catch(e){return unique(['Sin novedad','Conexión floja','Fuga detectada','Suciedad acumulada','Refrigerante bajo','Equipo requiere correctivo'].concat(REEFER_HALLAZGOS_ADICIONALES));}
  }
  function ensureAuto(s){
    if(!s.auto||typeof s.auto!=='object')s.auto=emptyAuto();
    if(!Array.isArray(s.auto.actividades))s.auto.actividades=[];
    if(!Array.isArray(s.auto.hallazgos))s.auto.hallazgos=[];
    if(!Array.isArray(s.auto.acciones))s.auto.acciones=[];
    // Compatibilidad con reportes anteriores: las acciones pasan a formar parte
    // de la única lista de actividades realizadas, sin duplicarlas.
    s.auto.actividades=unique(s.auto.actividades.concat(s.auto.acciones));
    s.auto.hallazgos=unique(s.auto.hallazgos);
    s.auto.acciones=[];
    return s.auto;
  }

  function smartEntry(s,key,title,suggestions,kind){
    ensureAuto(s);
    suggestions=unique(suggestions.concat(opcionesPersonalizadas(key,s)));
    const group=document.createElement('div');
    group.className='quick-group zg-v35-group '+(kind||'');
    const ttl=document.createElement('div');ttl.className='quick-title';ttl.textContent=title;
    const search=document.createElement('div');search.className='zg-v35-search';
    const wrap=document.createElement('div');wrap.className='zg-v35-search-wrap';
    const input=document.createElement('input');input.type='text';input.className='zg-v35-input';input.autocomplete='off';input.placeholder=key==='hallazgos'?'Escribe o busca un hallazgo':'Escribe o busca una actividad realizada';
    const menu=document.createElement('div');menu.className='zg-v35-menu';
    const ai=document.createElement('button');ai.type='button';ai.className='zg-v53-ai';ai.textContent='Detallar con IA';ai.title='Organiza lo escrito sin inventar información';
    const add=document.createElement('button');add.type='button';add.className='zg-v35-add';add.textContent='Agregar';
    const tags=document.createElement('div');tags.className='zg-v35-tags';
    const tip=document.createElement('div');tip.className='zg-v35-tip';tip.textContent='Toca el cuadro para ver las opciones. También puedes escribir una nueva y presionar Enter.';
    const aiNote=document.createElement('div');aiNote.className='zg-v53-ai-note';aiNote.textContent='La IA usa el tipo de equipo, el trabajo actual y la memoria registrada para ese mismo trabajo.';
    wrap.append(input,menu);search.append(wrap,ai,add);group.append(ttl,search,tags,tip,aiNote);
    let active=-1;

    function values(){return s.auto[key];}
    function setValues(v){s.auto[key]=unique(v);s.auto.limpiado=true;markChanged();}
    function refreshSuggestions(){suggestions=unique(suggestions.concat(opcionesPersonalizadas(key,s)));}
    function renderTags(){
      tags.innerHTML='';
      values().forEach(function(v){
        const b=document.createElement('button');b.type='button';b.className='qchip on';b.textContent=v;b.title='Quitar';
        b.addEventListener('click',function(){setValues(values().filter(function(x){return norm(x)!==norm(v);}));renderTags();renderMenu();});
        tags.appendChild(b);
      });
    }
    function matches(){
      refreshSuggestions();
      const q=norm(input.value);
      const selected=new Set(values().map(norm));
      return suggestions.filter(function(v){return !selected.has(norm(v))&&(!q||norm(v).includes(q));}).slice(0,18);
    }
    function renderMenu(){
      const rows=matches();menu.innerHTML='';active=-1;
      if(!rows.length){
        const empty=document.createElement('div');empty.className='zg-v35-empty';empty.textContent=clean(input.value)?'No hay coincidencias. Presiona Enter para conservar lo escrito.':'No quedan opciones disponibles para este trabajo.';menu.appendChild(empty);
      }else rows.forEach(function(v){
        const b=document.createElement('button');b.type='button';b.className='zg-v35-option';b.textContent=v;
        b.addEventListener('click',function(){addValue(v);});menu.appendChild(b);
      });
      menu.classList.add('show');
    }
    function addValue(raw){
      let v=clean(raw||input.value);if(!v){input.focus();return;}
      refreshSuggestions();
      const exact=suggestions.find(function(x){return norm(x)===norm(v);});
      const esNueva=!exact;
      if(exact)v=exact;
      if(!values().some(function(x){return norm(x)===norm(v);})){
        setValues(values().concat(v));
        if(esNueva){
          suggestions=unique(suggestions.concat(v));
          guardarOpcionPersonalizada(v,key,s);
        }
      }
      input.value='';renderTags();renderMenu();input.focus();
    }
    async function detallarConIa(){
      const original=clean(input.value);
      if(!original){
        input.focus();
        try{toast('Escribe primero una actividad o hallazgo breve.');}catch(e){}
        return;
      }
      const old=ai.textContent;ai.disabled=true;ai.textContent='Procesando...';
      try{
        const r=await fetch('mejorar_texto_ia.php',{
          method:'POST',
          headers:{'Content-Type':'application/json','Accept':'application/json'},
          credentials:'same-origin',
          body:JSON.stringify({
            modo:'opcion_tecnica',
            categoria:key,
            texto:original,
            etiqueta:title,
            tipo_equipo:document.getElementById('zgTipoEquipo')?.value||'',
            trabajo:clean(s.nombre||''),
            trabajo_clave:claveTrabajo(s),
            actividades:s.auto.actividades||[],
            hallazgos:s.auto.hallazgos||[],
            memoria_trabajo:opcionesPersonalizadas(key,s).slice(0,35),
            preinspeccion_id:String(document.getElementById('preinspeccionId')?.value||window.PREINSPECCION?.id||window.PREINSPECCION?.pre_id||''),
            token_continuacion:String(document.getElementById('tokenContinuacion')?.value||window.TOKEN_CONTINUACION||'')
          })
        });
        let d={};try{d=await r.json();}catch(e){}
        if(!r.ok||!d.ok||d.source!=='anthropic'||!clean(d.texto))throw new Error(clean(d.error)||'No se pudo obtener la redacción.');
        input.value=clean(d.texto);
        renderMenu();input.focus();
        try{toast('Texto detallado con IA. Revísalo y presiona Agregar.');}catch(e){}
      }catch(err){
        try{toast(err.message||'No se pudo usar la ayuda de IA.');}catch(e){}
      }finally{ai.disabled=false;ai.textContent=old;}
    }
    function move(step){
      const opts=Array.from(menu.querySelectorAll('.zg-v35-option'));if(!opts.length)return;
      active=(active+step+opts.length)%opts.length;opts.forEach(function(o,i){o.classList.toggle('active',i===active);});opts[active].scrollIntoView({block:'nearest'});
    }
    input.addEventListener('focus',renderMenu);
    input.addEventListener('input',renderMenu);
    input.addEventListener('keydown',function(e){
      if(e.key==='ArrowDown'){e.preventDefault();move(1);}
      else if(e.key==='ArrowUp'){e.preventDefault();move(-1);}
      else if(e.key==='Enter'||e.key===','){
        e.preventDefault();const opts=Array.from(menu.querySelectorAll('.zg-v35-option'));if(active>=0&&opts[active])addValue(opts[active].textContent);else addValue(input.value);
      }else if(e.key==='Escape'){menu.classList.remove('show');}
    });
    ai.addEventListener('click',detallarConIa);
    add.addEventListener('click',function(){addValue(input.value);});
    input.addEventListener('blur',function(){setTimeout(function(){menu.classList.remove('show');},180);});
    renderTags();
    return group;
  }

  // Mismo formato de detalle técnico para mantenimiento preventivo y correctivo,
  // tanto en reefer como en generador.
  try{
    CAMPOS.asistencia_tecnica=[{id:'detalle_tecnico',label:'Detalle técnico de la asistencia técnica',tipo:'area'}];
    CAMPOS.mantenimiento_productivo=[{id:'detalle_tecnico',label:'Detalle técnico del mantenimiento preventivo',tipo:'area'}];
    CAMPOS.mantenimiento_correctivo=[{id:'detalle_tecnico',label:'Detalle técnico del mantenimiento correctivo',tipo:'area'}];
    CAMPOS.instalacion_reefer=[{id:'detalle_tecnico',label:'Detalle técnico de la instalación de reefer',tipo:'area'}];
    CAMPOS.genset_mantenimiento_preventivo=[{id:'detalle_tecnico',label:'Detalle técnico del mantenimiento preventivo',tipo:'area'}];
    CAMPOS.genset_mantenimiento_correctivo=[{id:'detalle_tecnico',label:'Detalle técnico del mantenimiento correctivo',tipo:'area'}];
  }catch(e){}

  // Evita que los trabajos nuevos se llenen con decenas de opciones por defecto.
  window.aplicarPresetSilencioso=aplicarPresetSilencioso=function(s,nombre){
    if(!s.auto||typeof s.auto!=='object')s.auto=emptyAuto();
    ensureAuto(s);s.auto.plantilla=clean(nombre||'');
  };
  window.crearSeleccionTrabajo=crearSeleccionTrabajo=function(w){
    return {id:w.id,nombre:w.nombre,custom:!!w.custom,campos:{},detalle:'',photos:[],auto:emptyAuto(),maquinaAsignada:'',repuestosTrabajo:[],mantenimientoAdicional:{requiere:'',tipo:''}};
  };

  window.buildQuickAssistant=buildQuickAssistant=function(s){
    ensureAuto(s);
    const genset=isGenset();
    const activities=actividadesParaTrabajo(s,genset);
    const findings=unique((genset?GEN_HALLAZGOS:reeferFindings()).concat(opcionesPersonalizadas('hallazgos',s)));
    const box=document.createElement('div');box.className='quick-assistant zg-v35-assistant';
    const head=document.createElement('div');head.className='zg-v35-head';
    const text=document.createElement('div');
    text.innerHTML='<strong>Registro técnico del servicio</strong><small>Registra las acciones ejecutadas y los observaciones. También puedes escribir una opción propia.</small>';
    const clear=document.createElement('button');clear.type='button';clear.className='zg-v35-clear';clear.textContent='Limpiar datos';
    clear.addEventListener('click',function(){
      s.auto.actividades=[];s.auto.hallazgos=[];s.auto.acciones=[];s.auto.limpiado=true;markChanged();renderPanels();
      try{toast('Actividades y hallazgos limpiados.');}catch(e){}
    });
    head.append(text,clear);box.appendChild(head);
    const grid=document.createElement('div');grid.className='zg-v35-grid';
    grid.appendChild(smartEntry(s,'actividades','Actividades realizadas',activities,''));
    grid.appendChild(smartEntry(s,'hallazgos','Observaciones',findings,'warn'));
    box.appendChild(grid);
    if(!genset&&(s.id==='revision_tecnica'||norm(s.nombre).includes('revision')||norm(s.nombre).includes('pti'))){
      try{box.appendChild(buildPtiBox(s));}catch(e){}
    }
    return box;
  };

  function placeholders(){
    document.querySelectorAll('textarea[id*="mantenimiento_productivo_detalle_tecnico"],textarea[id*="genset_mantenimiento_preventivo_detalle_tecnico"]').forEach(function(x){
      x.placeholder='Describe las acciones ejecutadas, los componentes intervenidos y la condición final del equipo.';
    });
    document.querySelectorAll('textarea[id*="mantenimiento_correctivo_detalle_tecnico"],textarea[id*="genset_mantenimiento_correctivo_detalle_tecnico"]').forEach(function(x){
      x.placeholder='Describe la falla atendida, la intervención ejecutada y la condición final del equipo.';
    });
    document.querySelectorAll('textarea[id*="instalacion_reefer_detalle_tecnico"]').forEach(function(x){
      x.placeholder='Describe la instalación ejecutada, las conexiones realizadas, las mediciones registradas y la condición final del reefer.';
    });
  }
  const observer=new MutationObserver(placeholders);
  function init(){
    placeholders();
    try{observer.observe(document.body,{childList:true,subtree:true});}catch(e){}
    try{Object.values(state.selected||{}).forEach(ensureAuto);renderPanels();}catch(e){}
  }
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init);else init();
  window.addEventListener('load',function(){setTimeout(init,120);});
})();

(function(){
  function byId(id){ return document.getElementById(id); }
  function clean(v){ return String(v == null ? '' : v).trim(); }
  function enabled(){
    try{
      return !!PREINSPECCION && !ZG_PRE_EDIT_MODE && !ZG_EDIT_MODE && clean(PREINSPECCION.estado || 'abierto').toLowerCase() === 'abierto';
    }catch(e){ return false; }
  }
  if(!enabled()) return;

  const preId = String((PREINSPECCION && (PREINSPECCION.id || PREINSPECCION.pre_id)) || byId('preinspeccionId')?.value || '');
  const token = String(TOKEN_CONTINUACION || byId('tokenContinuacion')?.value || '');
  if(!preId) return;

  let dirty = false;
  let saving = false;
  let restoring = false;
  let timer = null;
  let lastSaved = '';

  function fmtDate(raw){
    if(!raw) return '';
    const d = new Date(String(raw).replace(' ','T'));
    if(Number.isNaN(d.getTime())) return String(raw);
    return d.toLocaleString('es-PE',{day:'2-digit',month:'2-digit',year:'numeric',hour:'2-digit',minute:'2-digit'});
  }

  function installUi(){
    const bar = document.querySelector('.actionbar-inner');
    if(!bar || byId('zgDraftTools')) return;
    const wrap = document.createElement('div');
    wrap.id='zgDraftTools';wrap.className='zg-draft-tools show';
    wrap.innerHTML='<button type="button" class="zg-draft-save" id="zgDraftSaveBtn">Guardar avance</button><span class="zg-draft-status" id="zgDraftStatus">El avance se guardará automáticamente</span>';
    const clear = byId('clearBtn');
    bar.insertBefore(wrap, clear || bar.firstChild);
    byId('zgDraftSaveBtn')?.addEventListener('click',function(){ saveDraft(true); });
  }

  function setStatus(text, cls){
    const el=byId('zgDraftStatus'); if(!el) return;
    el.textContent=text; el.className='zg-draft-status'+(cls?' '+cls:'');
  }

  function collectDraft(){
    if(typeof window.zgCollectReportSnapshot !== 'function') return null;
    const snap = window.zgCollectReportSnapshot();
    if(!snap || typeof snap !== 'object') return null;
    snap.version = 41;
    snap.kind = 'service_draft';
    snap.preinspeccionId = preId;
    snap.savedAt = new Date().toISOString();
    snap.preEvidence = [];
    if(snap.fields && typeof snap.fields === 'object'){
      Object.keys(snap.fields).forEach(function(id){
        const el=byId(id);
        if(el && el.closest && el.closest('#datosGeneralesCard')) delete snap.fields[id];
      });
    }
    return snap;
  }

  async function saveDraft(manual){
    if(saving || restoring) return false;
    const snap=collectDraft(); if(!snap) return false;
    saving=true; clearTimeout(timer);
    setStatus(manual?'Guardando avance...':'Guardando automáticamente...','saving');
    const fd=new FormData();
    fd.append('preinspeccion_id',preId);
    fd.append('token_continuacion',token);
    fd.append('datos_json',JSON.stringify(snap));
    try{
      const res=await fetch('guardar_borrador_servicio.php',{method:'POST',body:fd,credentials:'same-origin'});
      const out=await res.json();
      if(!res.ok || !out.ok) throw new Error(out.error || 'No se pudo guardar el avance');
      dirty=false; lastSaved=out.actualizado_en || '';
      setStatus('Avance guardado'+(lastSaved?' · '+fmtDate(lastSaved):''),'saved');
      if(manual && typeof toast==='function') toast('Avance del servicio guardado');
      return true;
    }catch(e){
      setStatus('No se pudo guardar el avance','error');
      if(manual) alert('No se pudo guardar el avance: '+e.message);
      return false;
    }finally{ saving=false; }
  }

  function schedule(){
    if(restoring) return;
    dirty=true; clearTimeout(timer);
    setStatus('Cambios pendientes de guardar','saving');
    timer=setTimeout(function(){ saveDraft(false); },4500);
  }

  function restoreDraft(){
    const pack = (typeof ZG_SERVICE_DRAFT!=='undefined' && ZG_SERVICE_DRAFT) ? ZG_SERVICE_DRAFT : null;
    if(!pack || !pack.snapshot || typeof window.zgRestoreReportSnapshot!=='function') return;
    restoring=true;
    try{
      window.zgRestoreReportSnapshot(pack.snapshot);
      lastSaved=pack.actualizado_en || '';
      setStatus('Avance recuperado'+(lastSaved?' · '+fmtDate(lastSaved):''),'saved');
      if(typeof toast==='function') toast('Se recuperó el avance guardado del servicio');
    }catch(e){ console.warn('No se pudo recuperar el borrador del servicio',e); }
    setTimeout(function(){restoring=false;dirty=false;},700);
  }

  function isSecondStageTarget(target){
    return !!(target && target.closest && target.closest('#trabajosServicioCard, #finalControlCard, #firmasCard'));
  }

  document.addEventListener('input',function(ev){ if(isSecondStageTarget(ev.target)) schedule(); },true);
  document.addEventListener('change',function(ev){ if(isSecondStageTarget(ev.target)) schedule(); },true);
  document.addEventListener('click',function(ev){
    if(!isSecondStageTarget(ev.target)) return;
    if(ev.target.closest('#zgDraftSaveBtn')) return;
    setTimeout(schedule,120);
  },true);

  window.addEventListener('load',function(){
    installUi();
    setTimeout(restoreDraft,1700);
    setTimeout(function(){
      if(new URLSearchParams(location.search).get('desde_edicion')==='1' && typeof toast==='function'){
        toast('Preliminar actualizada. Continúa llenando el servicio.');
      }
    },900);
  });
})();

(function(){
  'use strict';
  const COMMON=[
    {key:1,n:1,label:'Cable de alimentación',kind:'text',unit:'m'},
    {key:2,n:2,label:'Enchufe o receptáculo',kind:'text'},
    {key:3,n:3,label:'Interruptor termomagnético',kind:'text',unit:'A'},
    {key:4,n:4,label:'Transformador de control 440/24 V',kind:'okvolt',unit:'V'},
    {key:5,n:5,label:'Transformador de potencia 380/440 V',kind:'okvolt',unit:'V'},
    {key:6,n:6,label:'Transformador de potencia 220/440 V',kind:'okvolt',unit:'V'},
    {key:9,n:7,label:'Contactores principales',kind:'text'},
    {key:19,n:8,label:'Motor ventilador del evaporador 1',kind:'three',unit:'Ω'},
    {key:20,n:9,label:'Motor ventilador del evaporador 2',kind:'three',unit:'Ω'},
    {key:22,n:10,label:'Motor ventilador del condensador',kind:'three',unit:'Ω'},
    {key:23,n:11,label:'Compresor de refrigeración',kind:'three',unit:'Ω'},
    {key:24,n:12,label:'Resistencias de calefacción / heaters',kind:'three',unit:'Ω'},
    {key:26,n:13,label:'Número o versión de software',kind:'text'},
    {key:27,n:14,label:'Registrador de datos (DataCORDER)',kind:'select',options:['Sí','No']},
    {key:35,n:15,label:'Válvula moduladora',kind:'select',options:['25','50','75','100'],unit:'%'},
    {key:36,n:16,label:'Válvula de expansión',kind:'select',options:['Sí','No']},
    {key:37,n:17,label:'Nivel de refrigerante',kind:'select',options:['Lleno','A la mitad','Vacío']}
  ];
  const INSTALL=[
    {key:1,n:1,label:'Cable de alimentación',kind:'text',unit:'m'},
    {key:2,n:2,label:'Enchufe o receptáculo',kind:'text'},
    {key:19,n:3,label:'Motor ventilador del evaporador 1',kind:'three',unit:'Ω'},
    {key:20,n:4,label:'Motor ventilador del evaporador 2',kind:'three',unit:'Ω'},
    {key:21,n:5,label:'Motor ventilador del evaporador 3',kind:'three',unit:'Ω'},
    {key:22,n:6,label:'Motor ventilador del condensador',kind:'three',unit:'Ω'},
    {key:23,n:7,label:'Compresor de refrigeración',kind:'three',unit:'Ω'},
    {key:24,n:8,label:'Resistencias de calefacción / heaters',kind:'three',unit:'Ω'}
  ];
  window.ZG_REEFER_CHECKLIST_ITEMS=COMMON;
  function clean(v){return String(v==null?'':v).replace(/\s+/g,' ').trim()}
  function norm(v){return clean(v).toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'')}
  function isGenset(){const v=norm(document.getElementById('zgTipoEquipo')?.value);return v.includes('genset')||v.includes('generador')}
  function modeFor(s){
    if(!s||isGenset())return '';
    const id=norm(s.id),name=norm(s.nombre);
    if(id==='instalacion_reefer'||(name.includes('instalacion')&&name.includes('reefer')))return 'install';
    if(id==='asistencia_tecnica'||id==='mantenimiento_correctivo'||name.includes('asistencia tecnica')||name.includes('mantenimiento correctivo'))return 'common';
    return '';
  }
  function itemsFor(s){return modeFor(s)==='install'?INSTALL:(modeFor(s)==='common'?COMMON:[])}
  window.zgGetReeferChecklistItemsForWork=itemsFor;
  function ensureState(s){if(!s.reeferChecklist||typeof s.reeferChecklist!=='object')s.reeferChecklist={};return s.reeferChecklist}
  function mark(){try{if(window.zgroupMarcarCambio)window.zgroupMarcarCambio()}catch(e){}}
  function addOption(select,label,value){const o=document.createElement('option');o.value=value;o.textContent=label;select.appendChild(o)}
  function simpleControl(d,values,key){
    const wrap=document.createElement('div');wrap.className='zg-v48-simple-value';
    const raw=values[key];
    const unit=document.createElement('span');unit.className='zg-v48-unit';
    function syncUnit(mode){
      if(d.kind==='okvolt') unit.textContent=mode==='V'?'V':'—';
      else unit.textContent=d.unit||'—';
    }
    if(d.kind==='select'){
      const sel=document.createElement('select');addOption(sel,'Seleccionar','');(d.options||[]).forEach(v=>addOption(sel,d.unit==='%'?v+' %':v,v));
      sel.value=clean(raw&&typeof raw==='object'?raw.valor:raw);
      sel.addEventListener('change',function(){values[key]=sel.value;mark()});
      wrap.appendChild(sel);syncUnit(sel.value);
    }else if(d.kind==='okvolt'){
      const box=document.createElement('div');box.className='zg-v48-okvolt';
      const sel=document.createElement('select');addOption(sel,'Seleccionar','');addOption(sel,'OK','OK');addOption(sel,'Ingresar voltaje','V');
      const inp=document.createElement('input');inp.type='text';inp.inputMode='decimal';inp.placeholder='Voltios';
      let obj=(raw&&typeof raw==='object')?raw:{};
      if(typeof raw==='string'&&clean(raw)){
        if(/^ok(?:\s*v)?$/i.test(clean(raw)))obj={modo:'OK',valor:''};
        else obj={modo:'V',valor:clean(raw).replace(/\s*v\s*$/i,'')};
      }
      sel.value=clean(obj.modo);inp.value=clean(obj.valor);inp.hidden=sel.value!=='V';inp.disabled=sel.value!=='V';syncUnit(sel.value);
      const save=function(){values[key]={modo:sel.value,valor:inp.value};syncUnit(sel.value);mark()};
      sel.addEventListener('change',function(){inp.hidden=sel.value!=='V';inp.disabled=sel.value!=='V';if(sel.value!=='V')inp.value='';save();if(sel.value==='V')setTimeout(()=>inp.focus(),0)});
      inp.addEventListener('input',save);box.append(sel,inp);wrap.appendChild(box);
    }else{
      const inp=document.createElement('input');inp.type='text';inp.autocomplete='off';inp.placeholder=d.unit?'Ingresa solo el valor':'Escribe el resultado u observación';inp.value=clean(raw&&typeof raw==='object'?raw.valor:raw);
      inp.addEventListener('input',function(){values[key]=inp.value;mark()});wrap.appendChild(inp);syncUnit();
    }
    wrap.appendChild(unit);return wrap;
  }
  function build(s){
    const items=itemsFor(s);if(!items.length)return null;const values=ensureState(s);
    const details=document.createElement('details');details.className='zg-v48-checklist';details.open=true;
    const summary=document.createElement('summary');summary.innerHTML='<span>📋 Lista de inspección técnica reefer</span><small>'+(modeFor(s)==='install'?'Instalación de reefer':'Asistencia técnica / mantenimiento correctivo')+'</small>';
    const body=document.createElement('div');body.className='zg-v48-checklist-body';
    const simple=items.filter(d=>d.kind!=='three'),three=items.filter(d=>d.kind==='three');
    if(simple.length){const grid=document.createElement('div');grid.className='zg-v48-simple-grid';simple.forEach(function(d){const key=String(d.key);const card=document.createElement('div');card.className='zg-v48-simple-card';const head=document.createElement('div');head.className='zg-v48-simple-head';head.innerHTML='<span class="zg-v48-num">'+d.n+'</span><span>'+d.label+'</span>';card.append(head,simpleControl(d,values,key));grid.appendChild(card)});body.appendChild(grid)}
    if(three.length){const wrap=document.createElement('div');wrap.className='zg-v48-three-wrap';const table=document.createElement('table');table.className='zg-v48-three-table';table.innerHTML='<thead><tr><th>N°</th><th>Punto de inspección</th><th>L1</th><th>L2</th><th>L3</th><th>Unidad</th></tr></thead>';const tbody=document.createElement('tbody');three.forEach(function(d){const key=String(d.key);const val=(values[key]&&typeof values[key]==='object')?values[key]:{};const tr=document.createElement('tr');const n=document.createElement('td');n.className='n';n.textContent=d.n;const it=document.createElement('td');it.className='item';it.textContent=d.label;tr.append(n,it);['l1','l2','l3'].forEach(function(k){const td=document.createElement('td');const inp=document.createElement('input');inp.type='text';inp.inputMode='decimal';inp.placeholder=d.unit;inp.value=clean(val[k]);inp.addEventListener('input',function(){if(!values[key]||typeof values[key]!=='object')values[key]={};values[key][k]=inp.value;delete values[key].meg;mark()});td.appendChild(inp);tr.appendChild(td)});const u=document.createElement('td');u.className='unit';u.textContent=d.unit||'—';tr.appendChild(u);tbody.appendChild(tr)});table.appendChild(tbody);wrap.appendChild(table);body.appendChild(wrap)}
    details.append(summary,body);return details;
  }
  const old=window.makePanel||makePanel;
  window.makePanel=makePanel=function(s){const panel=old(s);try{panel.querySelectorAll('.zg-v42-checklist,.zg-v48-checklist').forEach(x=>x.remove());const card=build(s);if(card){const body=panel.querySelector('.panel-body');const assistant=body&&body.querySelector('.quick-assistant,.zg-v35-box,.zg-v38-box');if(assistant&&assistant.parentNode)assistant.insertAdjacentElement('afterend',card);else if(body)body.insertBefore(card,body.firstChild)}}catch(e){console.warn('Checklist reefer V48:',e)}return panel};
  document.addEventListener('change',function(ev){if(ev.target&&ev.target.id==='zgTipoEquipo')setTimeout(function(){try{renderPanels()}catch(e){}},80)},true);
})();

(function(){
  'use strict';
  const byId=id=>document.getElementById(id),clean=v=>String(v==null?'':v).replace(/\s+/g,' ').trim();
  function isG(){const v=clean(byId('zgTipoEquipo')?.value).toLowerCase();return v.includes('genset')||v.includes('generador')}
  function energyBox(){const e=byId('estadoEnergia');return e&&e.closest('.estado-box')}
  function syncView(){const box=energyBox(),e=byId('estadoEnergia'),hint=document.querySelector('.estado-inicial-pro .field-hint');if(box)box.classList.toggle('zg-energy-state-box',isG());if(e){e.disabled=isG();if(isG())e.value='';}if(hint)hint.textContent=isG()?'Selecciona el funcionamiento y la condición de alarma del generador antes de guardar la inspección preliminar.':'Selecciona las 3 condiciones del equipo antes de guardar la inspección preliminar.';compose()}
  function compose(){const a=clean(byId('estadoEncendido')?.value),b=clean(byId('estadoEnergia')?.value),c=clean(byId('estadoAlarma')?.value),d=clean(byId('alarmaEncontrada')?.value),h=byId('estadoInicial');const alarm=(c==='Con alarma'&&d)?c+': '+d:c;let value='';if(isG()){if(a&&c)value=a+' / '+alarm;}else if(a&&b&&c)value=a+' / '+b+' / '+alarm;if(h&&value)h.value=value;return value||(h?clean(h.value):'')}
  function msg(id,t){try{if(typeof fieldMsg==='function')return fieldMsg(id,t)}catch(e){}const x=byId(id);if(x){x.classList.add('input-error');x.scrollIntoView({behavior:'smooth',block:'center'})}try{toast(t)}catch(e){alert(t)}return false}
  function validate(){const a=clean(byId('estadoEncendido')?.value),b=clean(byId('estadoEnergia')?.value),c=clean(byId('estadoAlarma')?.value),d=clean(byId('alarmaEncontrada')?.value);if(!a)return msg('estadoEncendido','Selecciona si el equipo estaba encendido o apagado.');if(!isG()&&!b)return msg('estadoEnergia','Selecciona si el equipo tenía suministro eléctrico o no.');if(!c)return msg('estadoAlarma','Selecciona si el equipo tenía alarma o no.');if(c==='Con alarma'&&!d)return msg('alarmaEncontrada','Escribe el código o número de alarma encontrado.');compose();return true}
  try{window.zgroupEstadoInicialCompuesto=compose;zgroupEstadoInicialCompuesto=compose}catch(e){}
  try{window.validarEstadoInicialTriple=validate;validarEstadoInicialTriple=validate}catch(e){}
  document.addEventListener('change',function(e){if(e.target&&['zgTipoEquipo','estadoEncendido','estadoEnergia','estadoAlarma'].includes(e.target.id))setTimeout(syncView,0)},true);
  document.addEventListener('input',function(e){if(e.target&&e.target.id==='alarmaEncontrada')compose()},true);
  const init=()=>{syncView();try{window.zgroupEstadoInicialCompuesto=compose;zgroupEstadoInicialCompuesto=compose;window.validarEstadoInicialTriple=validate;validarEstadoInicialTriple=validate}catch(e){}};
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init);else init();window.addEventListener('load',()=>setTimeout(init,200));new MutationObserver(()=>setTimeout(syncView,0)).observe(document.documentElement,{childList:true,subtree:true});
})();

(function(){
  'use strict';
  const ROWS=[
    {key:'corriente',label:'Corriente de línea',unit:'A'},
    {key:'motor_evap1',label:'8. Motor ventilador del evaporador 1',unit:'Ω'},
    {key:'motor_evap2',label:'9. Motor ventilador del evaporador 2',unit:'Ω'},
    {key:'motor_cond',label:'10. Motor ventilador del condensador',unit:'Ω'},
    {key:'compresor',label:'11. Compresor de refrigeración',unit:'Ω'},
    {key:'resistencias',label:'12. Resistencias de calefacción / heaters',unit:'Ω'}
  ];
  window.ZG_REEFER_PARAMETROS_ROWS=ROWS;
  function clean(v){return String(v==null?'':v).replace(/\s+/g,' ').trim()}
  function norm(v){return clean(v).toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'')}
  function isGenset(){const v=norm(document.getElementById('zgTipoEquipo')?.value);return v.includes('genset')||v.includes('generador')}
  function applies(s){
    if(!s||isGenset())return false;
    const id=norm(s.id),name=norm(s.nombre);
    return id==='instalacion_reefer'||id==='mantenimiento_productivo'
      ||(name.includes('instalacion')&&name.includes('reefer'))
      ||name.includes('mantenimiento preventivo');
  }
  function ensure(s){
    if(!s.parametrosReefer||typeof s.parametrosReefer!=='object')s.parametrosReefer={};
    return s.parametrosReefer;
  }
  function mark(){try{if(window.zgroupMarcarCambio)window.zgroupMarcarCambio()}catch(e){}}
  function build(s){
    if(!applies(s))return null;
    const values=ensure(s);
    const box=document.createElement('div');box.className='zg-v51-parametros';
    const head=document.createElement('div');head.className='zg-v51-parametros-head';
    head.innerHTML='⚡ Corriente de línea y resistencias del reefer<span class="zg-v51-parametros-sub">La corriente de línea se registra en A. Los motores, compresor y heaters se registran en Ω por L1, L2 y L3.</span>';
    const wrap=document.createElement('div');wrap.className='zg-v51-parametros-wrap';
    const table=document.createElement('table');table.className='zg-v51-parametros-table';
    table.innerHTML='<thead><tr><th>PARÁMETRO</th><th>L1</th><th>L2</th><th>L3</th><th>UNIDAD</th></tr></thead>';
    const tbody=document.createElement('tbody');
    ROWS.forEach(function(r){
      const row=values[r.key]&&typeof values[r.key]==='object'?values[r.key]:{};
      const tr=document.createElement('tr');
      const tdP=document.createElement('td');tdP.className='param';tdP.textContent=r.label;tr.appendChild(tdP);
      ['l1','l2','l3'].forEach(function(k){
        const td=document.createElement('td');
        const inp=document.createElement('input');inp.type='text';inp.inputMode='decimal';inp.placeholder=r.unit;inp.value=clean(row[k]);
        inp.addEventListener('input',function(){
          if(!values[r.key]||typeof values[r.key]!=='object')values[r.key]={};
          values[r.key][k]=inp.value;mark();
        });
        td.appendChild(inp);tr.appendChild(td);
      });
      const tdU=document.createElement('td');tdU.className='unit';tdU.textContent=r.unit;tr.appendChild(tdU);
      tbody.appendChild(tr);
    });
    table.appendChild(tbody);wrap.appendChild(table);box.append(head,wrap);return box;
  }
  const old=window.makePanel||makePanel;
  window.makePanel=makePanel=function(s){
    const panel=old(s);
    try{
      panel.querySelectorAll('.zg-v51-parametros').forEach(x=>x.remove());
      const card=build(s);
      if(card){
        const body=panel.querySelector('.panel-body');
        const checklist=body&&body.querySelector('.zg-v48-checklist');
        const assistant=body&&body.querySelector('.quick-assistant,.zg-v35-assistant');
        if(checklist)checklist.insertAdjacentElement('afterend',card);
        else if(assistant)assistant.insertAdjacentElement('afterend',card);
        else if(body)body.insertBefore(card,body.firstChild);
      }
    }catch(e){console.warn('Parámetros reefer V51:',e)}
    return panel;
  };
  document.addEventListener('change',function(ev){
    if(ev.target&&ev.target.id==='zgTipoEquipo')setTimeout(function(){try{renderPanels()}catch(e){}},80);
  },true);
})();

(function(){
  'use strict';
  const byId=id=>document.getElementById(id);
  const clean=v=>String(v==null?'':v).replace(/\s+/g,' ').trim();
  const norm=v=>clean(v).toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'');

  function selectedWorks(){
    try{return Object.values((typeof state!=='undefined'&&state.selected)?state.selected:{})}catch(e){return []}
  }
  function isReefer(){
    const t=norm(byId('zgTipoEquipo')?.value);
    return !(t.includes('genset')||t.includes('generador'));
  }
  function relevantWork(s){
    return !!s;
  }
  function relevantWorks(){return isReefer()?selectedWorks().filter(relevantWork):[]}
  function modality(){return norm(byId('zgModalidadComercial')?.value)}
  function rentalMandatory(){return relevantWorks().length>0&&modality().includes('alquiler')}
  function saleRule(){return relevantWorks().length>0&&modality().includes('venta')}
  function materialsCount(onlyRelevant){
    const list=onlyRelevant?relevantWorks():selectedWorks();
    return list.reduce(function(total,s){return total+(Array.isArray(s&&s.repuestosTrabajo)?s.repuestosTrabajo.filter(function(r){return clean(r&&r.detalle)}).length:0)},0)
  }
  function setDecision(value){
    const h=byId('requiereRepuesto'),yes=byId('repuestoSiBtn'),no=byId('repuestoNoBtn');
    if(h)h.value=value?'si':'no';
    if(yes)yes.classList.toggle('on',!!value);
    if(no)no.classList.toggle('on',!value);
  }
  function ensureHiddenNote(){
    let hidden=byId('zgNotaRepuestoComercial');
    if(!hidden){hidden=document.createElement('input');hidden.type='hidden';hidden.id='zgNotaRepuestoComercial';(byId('trabajosServicioCard')||document.body).appendChild(hidden)}
    return hidden;
  }
  function decoratePanels(){
    const works=selectedWorks(),panels=[...document.querySelectorAll('#panels .panel')];
    panels.forEach(function(panel,idx){
      let note=panel.querySelector('.zg-work-commercial-rule');
      const s=works[idx],applies=relevantWork(s)&&isReefer();
      if(!applies){if(note)note.remove();return}
      if(!note){note=document.createElement('div');note.className='zg-work-commercial-rule';const materials=panel.querySelector('.zg-work-materials');if(materials)materials.insertBefore(note,materials.firstChild);else panel.querySelector('.panel-body')?.prepend(note)}
      if(modality().includes('alquiler')){
        note.className='zg-work-commercial-rule alquiler';
        note.textContent='Alquiler: debe registrarse la pieza que será reemplazada como parte de la atención.';
      }else if(modality().includes('venta')){
        note.className='zg-work-commercial-rule venta';
        note.textContent='Venta: los repuestos seleccionados quedan pendientes de cotización y se atenderán en un trabajo futuro; no se registran como reemplazados en este servicio.';
      }else note.remove();
    });
  }
  function syncRule(){
    const no=byId('repuestoNoBtn'),hiddenNote=ensureHiddenNote();
    const relevantMaterials=materialsCount(true),allMaterials=materialsCount(false);
    if(rentalMandatory()){
      setDecision(true);
      if(no){no.disabled=true;no.classList.add('zg-disabled-rule');no.setAttribute('aria-disabled','true')}
      hiddenNote.value='Por tratarse de un servicio de alquiler, debe registrarse la pieza que será reemplazada.';
    }else{
      if(no){no.disabled=false;no.classList.remove('zg-disabled-rule');no.removeAttribute('aria-disabled')}
      if(saleRule()){
        setDecision(relevantMaterials>0);
        hiddenNote.value=relevantMaterials>0?'Se requerirá de una cotización para el repuesto indicado. El cambio se realizará en un trabajo futuro.':'';
      }else{
        if(allMaterials>0)setDecision(true);
        hiddenNote.value='';
      }
    }
    decoratePanels();
  }
  function syncVisibleMaterials(){
    try{if(typeof window.zgSyncWorkMaterialsForPdf==='function')window.zgSyncWorkMaterialsForPdf()}catch(e){}
  }
  function firstRelevantMaterialsBox(){
    const works=selectedWorks(),panels=[...document.querySelectorAll('#panels .panel')];
    for(let i=0;i<works.length;i++)if(relevantWork(works[i]))return panels[i]?.querySelector('.zg-work-materials')||panels[i];
    return document.querySelector('#panels .zg-work-materials');
  }
  function countManualRepuestos(){
    const manual=clean(byId('repuestosManual')?.value);
    if(!manual)return 0;
    return manual.split(/\n/).map(function(l){return clean(l)}).filter(Boolean).length;
  }
  function countFinalTableRepuestos(){
    return [...document.querySelectorAll('#repuestosSelectedList .zg-repuestos-table tbody tr')].filter(function(r){
      return clean(r.querySelector('.zg-rep-detail2')?.value);
    }).length;
  }
  function countAllMaterials(){
    return materialsCount(true)+countManualRepuestos()+countFinalTableRepuestos();
  }
  function showError(msg){
    const box=firstRelevantMaterialsBox();
    if(box){box.style.boxShadow='0 0 0 3px rgba(191,70,70,.18)';box.scrollIntoView({behavior:'smooth',block:'center'});setTimeout(function(){box.style.boxShadow=''},1600);setTimeout(function(){try{box.querySelector('.zg-work-material-search input')?.focus()}catch(e){}},220)}
    try{if(typeof toast==='function')toast(msg);else alert(msg)}catch(e){alert(msg)}
    return false;
  }
  function validation(){
    syncVisibleMaterials();syncRule();
    const commercial=rentalMandatory()||saleRule();
    if(commercial&&countAllMaterials()<1){
      if(rentalMandatory())return showError('En alquiler debes registrar la pieza que será reemplazada en el servicio reefer.');
      return showError('En venta debes registrar al menos un repuesto pendiente de cotización.');
    }
    if(clean(byId('requiereRepuesto')?.value)==='si'&&countAllMaterials()<1){
      const ta=byId('repuestosManual'),err=byId('repuestosManualError');
      if(ta)ta.classList.add('input-error');
      if(err){err.textContent='Agrega al menos un material requerido.';err.classList.add('show')}
      try{if(typeof toast==='function')toast('Agrega el material requerido');else alert('Agrega el material requerido')}catch(e){alert('Agrega el material requerido')}
      const card=byId('repuestosCard');if(card)card.scrollIntoView({behavior:'smooth',block:'center'});
      return false;
    }
    return true;
  }
  try{window.validarRepuestos=validation;validarRepuestos=validation}catch(e){}

  document.addEventListener('click',function(ev){
    const no=ev.target&&ev.target.closest?ev.target.closest('#repuestoNoBtn'):null;
    if(no&&rentalMandatory()){
      ev.preventDefault();ev.stopImmediatePropagation();setDecision(true);syncRule();
      try{if(typeof toast==='function')toast('En alquiler debes registrar la pieza reemplazada.')}catch(e){}
    }
  },true);
  document.addEventListener('change',function(ev){
    if(ev.target&&['zgModalidadComercial','zgTipoEquipo'].includes(ev.target.id))setTimeout(syncRule,20);
  },true);
  document.addEventListener('input',function(ev){
    if(ev.target&&ev.target.closest&&ev.target.closest('.zg-work-material-table'))setTimeout(syncRule,20);
  },true);
  document.addEventListener('click',function(ev){
    if(ev.target&&ev.target.closest&&ev.target.closest('.work-card,.zg-work-material-option,.zg-work-material-table .del'))setTimeout(syncRule,80);
  },true);
  const init=function(){syncRule();[250,700,1400,2600].forEach(function(ms){setTimeout(syncRule,ms)})};
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init);else init();
  window.addEventListener('load',init);
})();

(function(){
  'use strict';
  const byId=id=>document.getElementById(id);
  const clean=v=>String(v==null?'':v).replace(/\s+/g,' ').trim();
  const norm=v=>clean(v).toUpperCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'').replace(/[^A-Z0-9]+/g,' ').trim();
  let busy=false;

  function works(){
    try{return Object.values((typeof state!=='undefined'&&state.selected)?state.selected:{})}catch(e){return []}
  }
  function reefer(){
    const t=norm(byId('zgTipoEquipo')?.value);
    return !(t.includes('GENSET')||t.includes('GENERADOR'));
  }
  function modality(){return norm(byId('zgModalidadComercial')?.value)}
  function isSale(){return modality().includes('VENTA')}
  function isRental(){return modality().includes('ALQUILER')}
  function inferUnit(detail,code){
    try{return clean(window.zgInferUnidadMaterial?window.zgInferUnidadMaterial(detail,code):'und')||'und'}catch(e){return 'und'}
  }
  function controllerKey(work){
    let brand=clean(byId('marcaEquipo')?.value),controller=clean(byId('controladorEquipo')?.value);
    const target=clean(work&&work.maquinaAsignada);
    if(/^M[1-5]$/.test(target)){
      const i=target.slice(1);
      brand=clean(byId('zgMachineBrand'+i)?.value)||brand;
      controller=clean(byId('zgMachineController'+i)?.value)||controller;
    }
    const joined=norm(brand+' '+controller);
    if(joined.includes('STAR COOL')&&joined.includes('CIM 6'))return 'STAR COOL CIM 6';
    if(joined.includes('STAR COOL')&&joined.includes('CIM 5'))return 'STAR COOL CIM 5';
    if(joined.includes('MP5000')||joined.includes('MP 5000'))return 'TK MP5000';
    if(joined.includes('MP4000')||joined.includes('MP 4000'))return 'TK MP4000';
    if(joined.includes('CARRIER'))return 'CARRIER';
    if(joined.includes('DAIKIN'))return 'DAIKIN';
    return '';
  }
  function exactMatch(detail,work){
    const target=norm(detail);if(!target||!window.ZG_CATALOGOS_POR_CONTROLADOR)return null;
    const catalogs=window.ZG_CATALOGOS_POR_CONTROLADOR;
    const key=controllerKey(work);
    const active=key&&Array.isArray(catalogs[key])?catalogs[key]:[];
    const inActive=active.find(r=>norm(r&&r.detalle)===target&&clean(r&&r.codigo));
    if(inActive)return {codigo:clean(inActive.codigo),detalle:clean(inActive.detalle),unidad:clean(inActive.unidad)||inferUnit(inActive.detalle,inActive.codigo)};
    const unique=new Map();
    Object.values(catalogs).forEach(arr=>(Array.isArray(arr)?arr:[]).forEach(r=>{
      if(norm(r&&r.detalle)!==target||!clean(r&&r.codigo))return;
      const code=clean(r.codigo);unique.set(norm(code),{codigo:code,detalle:clean(r.detalle),unidad:clean(r.unidad)||inferUnit(r.detalle,code)});
    }));
    return unique.size===1?[...unique.values()][0]:null;
  }
  function reconcileItem(item,work){
    if(!item||!clean(item.detalle))return false;
    const found=exactMatch(item.detalle,work);if(!found)return false;
    let changed=false;
    if(clean(item.codigo)!==found.codigo){item.codigo=found.codigo;changed=true}
    if(!clean(item.unidad)){item.unidad=found.unidad;changed=true}
    return changed;
  }
  function reconcileWorkTables(){
    const list=works(),panels=[...document.querySelectorAll('#panels .panel')];
    list.forEach((work,idx)=>{
      if(!Array.isArray(work.repuestosTrabajo))work.repuestosTrabajo=[];
      const panel=panels[idx];
      work.repuestosTrabajo.forEach((item,rowIdx)=>{
        const changed=reconcileItem(item,work);
        const row=panel?.querySelectorAll('.zg-work-material-table tbody tr')[rowIdx];
        if(row&&!row.querySelector('.zg-work-material-empty')){
          const codeNode=row.querySelector('td[data-label="Código"] b,td:first-child b');
          if(codeNode&&clean(item.codigo)){codeNode.textContent=item.codigo;codeNode.classList.remove('empty')}
          const unitNode=row.querySelector('.unit');if(unitNode&&clean(item.unidad))unitNode.textContent=item.unidad;
        }
        if(changed){try{if(window.zgroupMarcarCambio)window.zgroupMarcarCambio()}catch(e){}}
      });
    });
  }
  function reconcileFinalTable(){
    const tableRows=[...document.querySelectorAll('#repuestosSelectedList .zg-repuestos-table tbody tr')];
    let internal=[];
    try{internal=window.zgRepuestosTablaFinal&&typeof window.zgRepuestosTablaFinal.materiales==='function'?window.zgRepuestosTablaFinal.materiales():[]}catch(e){}
    let changed=false;
    tableRows.forEach((row,idx)=>{
      const detail=clean(row.querySelector('.zg-rep-detail2')?.value);
      const found=exactMatch(detail,null);if(!found)return;
      const codeNode=row.querySelector('.zg-rep-code2');
      if(codeNode&&clean(codeNode.textContent).replace(/^Sin código$/i,'')!==found.codigo){codeNode.textContent=found.codigo;codeNode.classList.remove('empty');changed=true}
      const unitNode=row.querySelector('.zg-rep-unit2');if(unitNode&&!clean(unitNode.textContent)){unitNode.textContent=found.unidad;changed=true}
      if(Array.isArray(internal)&&internal[idx]){
        if(clean(internal[idx].codigo)!==found.codigo){internal[idx].codigo=found.codigo;changed=true}
        if(!clean(internal[idx].unidad))internal[idx].unidad=found.unidad;
      }
    });
    if(Array.isArray(window.repuestosSeleccionados))window.repuestosSeleccionados.forEach(x=>{if(reconcileItem(x,null))changed=true});
    if(changed){
      try{if(window.zgRepuestosTablaFinal&&typeof window.zgRepuestosTablaFinal.guardar==='function')window.zgRepuestosTablaFinal.guardar()}catch(e){}
      try{if(window.zgroupMarcarCambio)window.zgroupMarcarCambio()}catch(e){}
    }
  }
  function countMaterials(){
    return works().reduce((n,w)=>n+(Array.isArray(w&&w.repuestosTrabajo)?w.repuestosTrabajo.filter(x=>clean(x&&x.detalle)).length:0),0)
      + [...document.querySelectorAll('#repuestosSelectedList .zg-repuestos-table tbody tr')].filter(r=>clean(r.querySelector('.zg-rep-detail2')?.value)).length;
  }
  function dispatch(el,type='change'){
    if(!el)return;try{el.dispatchEvent(new Event(type,{bubbles:true}))}catch(e){}
  }
  function setFollowup(){
    if(!reefer()||countMaterials()<1)return;
    const req=byId('zgRequiereOtroMantenimiento'),type=byId('zgTipoOtroMantenimiento'),reason=byId('zgMotivoOtroMantenimiento');
    if(req&&req.value!=='Sí'){req.value='Sí';dispatch(req)}
    if(type&&!clean(type.value)){type.value='Correctivo';dispatch(type)}
    const msg=isSale()
      ?'Se requerirá de una cotización para el repuesto indicado. El cambio se realizará en un trabajo futuro, mediante una nueva asistencia técnica, mantenimiento preventivo, mantenimiento correctivo o instalación, según corresponda.'
      :'Por tratarse de un servicio de alquiler, el repuesto indicado debe ser reemplazado. Se requiere programar o completar la intervención correspondiente.';
    if(reason&&clean(reason.value)!==msg){reason.value=msg;dispatch(reason,'input')}
    const hidden=byId('requiereRepuesto');if(hidden)hidden.value='si';
    byId('repuestoSiBtn')?.classList.add('on');byId('repuestoNoBtn')?.classList.remove('on');
  }
  function updateMaterialHeadings(){
    document.querySelectorAll('#panels .zg-work-materials').forEach(box=>{
      const h=box.querySelector('h4'),p=box.querySelector('.zg-work-materials-head p');
      if(isSale()){
        if(h)h.textContent='🧰 Repuestos requeridos para cotización futura';
        if(p)p.textContent='Los repuestos seleccionados no se registran como reemplazados en este servicio. Se cotizarán y atenderán en un trabajo futuro.';
      }else if(isRental()){
        if(h)h.textContent='🧰 Materiales / repuestos para reemplazo';
        if(p)p.textContent='Registra la pieza que debe ser reemplazada en el servicio de alquiler y ajusta la cantidad en la tabla.';
      }
    });
    document.querySelectorAll('#panels .zg-work-commercial-rule').forEach(note=>{
      if(isSale()){
        note.className='zg-work-commercial-rule venta';
        note.textContent='Venta: se requerirá una cotización para el repuesto indicado y el cambio se realizará en un trabajo futuro.';
      }else if(isRental()){
        note.className='zg-work-commercial-rule alquiler';
        note.textContent='Alquiler: la pieza indicada debe ser reemplazada como parte de la atención.';
      }
    });
  }
  function syncAll(){
    if(busy)return;busy=true;
    try{reconcileWorkTables();reconcileFinalTable();updateMaterialHeadings();setFollowup();try{if(typeof window.zgSyncWorkMaterialsForPdf==='function')window.zgSyncWorkMaterialsForPdf()}catch(e){}}finally{busy=false}
  }
  window.zgActualizarCodigosMateriales=syncAll;
  document.addEventListener('input',ev=>{
    if(ev.target?.matches?.('.zg-rep-detail2,.zg-work-material-table .detail'))setTimeout(syncAll,80);
  },true);
  document.addEventListener('change',ev=>{
    if(ev.target&&['zgModalidadComercial','zgTipoEquipo','marcaEquipo','controladorEquipo'].includes(ev.target.id))setTimeout(syncAll,30);
  },true);
  document.addEventListener('click',ev=>{
    if(ev.target?.closest?.('.zg-work-material-option,.zg-work-material-table .del,.zg-rep-del2,#repuestoSiBtn,#repuestoNoBtn,.work-card'))setTimeout(syncAll,120);
  },true);
  const observer=new MutationObserver(()=>setTimeout(syncAll,80));
  function init(){
    const root=byId('panels')||document.body;observer.observe(root,{childList:true,subtree:true});
    [50,250,700,1400,2600].forEach(ms=>setTimeout(syncAll,ms));
  }
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init);else init();
  window.addEventListener('load',()=>setTimeout(syncAll,120));
})();

(function(){
  'use strict';
  const byId=id=>document.getElementById(id);
  const clean=v=>String(v==null?'':v).replace(/\s+/g,' ').trim();
  const norm=v=>clean(v).toUpperCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'').replace(/[^A-Z0-9]+/g,' ').trim();
  const compact=v=>norm(v).replace(/\s+/g,'');
  let running=false, queued=false, observerStarted=false;

  function readConst(name){
    try{
      if(name==='REPUESTOS_CATALOGO' && typeof REPUESTOS_CATALOGO!=='undefined') return REPUESTOS_CATALOGO;
      if(name==='REPUESTOS_REEFER_CATALOGO' && typeof REPUESTOS_REEFER_CATALOGO!=='undefined') return REPUESTOS_REEFER_CATALOGO;
      if(name==='REPUESTOS_GENSET_CATALOGO' && typeof REPUESTOS_GENSET_CATALOGO!=='undefined') return REPUESTOS_GENSET_CATALOGO;
    }catch(e){}
    return [];
  }
  function inferUnit(detail,code){
    try{return clean(window.zgInferUnidadMaterial?window.zgInferUnidadMaterial(detail,code):'und')||'und';}catch(e){return 'und';}
  }
  function controllerKey(work){
    let brand=clean(byId('marcaEquipo')&&byId('marcaEquipo').value);
    let controller=clean(byId('controladorEquipo')&&byId('controladorEquipo').value);
    const target=clean(work&&work.maquinaAsignada);
    if(/^M[1-5]$/i.test(target)){
      const i=target.slice(1);
      brand=clean(byId('zgMachineBrand'+i)&&byId('zgMachineBrand'+i).value)||brand;
      controller=clean(byId('zgMachineController'+i)&&byId('zgMachineController'+i).value)||controller;
    }
    const joined=norm(brand+' '+controller);
    if(joined.includes('STAR COOL')&&joined.includes('CIM 6'))return 'STAR COOL CIM 6';
    if(joined.includes('STAR COOL')&&joined.includes('CIM 5'))return 'STAR COOL CIM 5';
    if(joined.includes('MP5000')||joined.includes('MP 5000'))return 'TK MP5000';
    if(joined.includes('MP4000')||joined.includes('MP 4000'))return 'TK MP4000';
    if(joined.includes('CARRIER'))return 'CARRIER';
    if(joined.includes('DAIKIN'))return 'DAIKIN';
    if(joined.includes('SG 3000'))return 'GENSET SG-3000';
    if(joined.includes('SG 5000'))return 'GENSET SG-5000';
    return '';
  }
  function keyFromDetail(detail){
    const d=norm(detail);
    if(d.includes('CIM 6'))return 'STAR COOL CIM 6';
    if(d.includes('CIM 5'))return 'STAR COOL CIM 5';
    if(d.includes('MP5000')||d.includes('MP 5000'))return 'TK MP5000';
    if(d.includes('MP4000')||d.includes('MP 4000'))return 'TK MP4000';
    if(d.includes('SG 3000'))return 'GENSET SG-3000';
    if(d.includes('SG 5000'))return 'GENSET SG-5000';
    return '';
  }
  function maps(){
    try{return window.ZG_CATALOGOS_POR_CONTROLADOR&&typeof window.ZG_CATALOGOS_POR_CONTROLADOR==='object'?window.ZG_CATALOGOS_POR_CONTROLADOR:{};}catch(e){return {};}
  }
  function entry(raw,source,priority){
    if(!raw)return null;
    const codigo=clean(raw.codigo||raw.code||'');
    const detalle=clean(raw.detalle||raw.nombre||raw.material||raw.descripcion||'');
    if(!codigo||!detalle)return null;
    return {codigo,detalle,unidad:clean(raw.unidad||'')||inferUnit(detalle,codigo),source:source||'',priority:priority||9};
  }
  function addEntries(dst,arr,source,priority){
    (Array.isArray(arr)?arr:[]).forEach(r=>{const e=entry(r,source,priority);if(e)dst.push(e);});
  }
  function allEntries(work,detail){
    const out=[];
    const catalogs=maps();
    const active=controllerKey(work)||keyFromDetail(detail);
    if(active&&Array.isArray(catalogs[active]))addEntries(out,catalogs[active],active,0);
    Object.keys(catalogs).forEach(k=>{if(k!==active)addEntries(out,catalogs[k],k,3);});
    addEntries(out,readConst('REPUESTOS_REEFER_CATALOGO'),'BD REEFER',4);
    addEntries(out,readConst('REPUESTOS_CATALOGO'),'CATÁLOGO GENERAL',5);
    addEntries(out,readConst('REPUESTOS_GENSET_CATALOGO'),'BD GENSET',5);
    const seen=new Set(),dedup=[];
    out.sort((a,b)=>a.priority-b.priority).forEach(e=>{
      const k=norm(e.codigo)+'|'+norm(e.detalle);
      if(seen.has(k))return;
      seen.add(k);dedup.push(e);
    });
    return {active,items:dedup};
  }
  function uniqueByCode(items){
    const map=new Map();
    items.forEach(x=>map.set(norm(x.codigo),x));
    return map.size===1?[...map.values()][0]:null;
  }
  function tokenScore(a,b){
    const A=new Set(norm(a).split(' ').filter(x=>x.length>1));
    const B=new Set(norm(b).split(' ').filter(x=>x.length>1));
    if(!A.size||!B.size)return 0;
    let common=0;A.forEach(x=>{if(B.has(x))common++;});
    return (2*common)/(A.size+B.size);
  }
  function resolve(detail,work,currentCode){
    detail=clean(detail);if(!detail)return null;
    const data=allEntries(work,detail),items=data.items;
    const target=norm(detail),targetCompact=compact(detail);
    if(clean(currentCode)){
      const byCode=items.find(x=>norm(x.codigo)===norm(currentCode));
      if(byCode)return byCode;
    }
    let found=items.filter(x=>norm(x.detalle)===target);
    if(found.length){
      const active=found.filter(x=>x.priority===0);
      return active[0]||uniqueByCode(found);
    }
    found=items.filter(x=>compact(x.detalle)===targetCompact);
    if(found.length){
      const active=found.filter(x=>x.priority===0);
      return active[0]||uniqueByCode(found);
    }
    // Solo se acepta coincidencia aproximada cuando es clara y no puede asignar un código equivocado.
    const scored=items.map(x=>({x,score:tokenScore(detail,x.detalle)})).filter(z=>z.score>=0.92).sort((a,b)=>b.score-a.score||a.x.priority-b.x.priority);
    if(scored.length){
      const best=scored[0].score;
      const bestItems=scored.filter(z=>Math.abs(z.score-best)<0.0001).map(z=>z.x);
      const active=bestItems.filter(x=>x.priority===0);
      return active[0]||uniqueByCode(bestItems);
    }
    return null;
  }
  function repairItem(item,work){
    if(!item||!clean(item.detalle))return false;
    const r=resolve(item.detalle,work,item.codigo);
    if(!r)return false;
    let changed=false;
    if(clean(item.codigo)!==r.codigo){item.codigo=r.codigo;changed=true;}
    if(!clean(item.unidad)){item.unidad=r.unidad;changed=true;}
    return changed;
  }
  function parseLine(line){
    const p=String(line||'').split('|').map(clean);
    if(!p.join(''))return null;
    let codigo='',detalle='',cantidad='1',unidad='';
    if(p.length>=4){codigo=p[0]==='-'?'':p[0];cantidad=p[p.length-2]||'1';unidad=p[p.length-1]||'';detalle=p.slice(1,-2).join(' | ');}
    else if(p.length===3){codigo=p[0]==='-'?'':p[0];detalle=p[1]||'';cantidad=p[2]||'1';}
    else if(p.length===2){codigo=p[0]==='-'?'':p[0];detalle=p[1]||'';}
    else detalle=p[0]||'';
    if(!detalle)return null;
    return {codigo,detalle,cantidad:String(cantidad||'1').replace(/[^0-9]/g,'')||'1',unidad:unidad||inferUnit(detalle,codigo)};
  }
  function serialize(items){
    return items.filter(x=>clean(x&&x.detalle)).map(x=>(clean(x.codigo)||'-')+' | '+clean(x.detalle)+' | '+(String(x.cantidad||'1').replace(/[^0-9]/g,'')||'1')+' | '+clean(x.unidad||inferUnit(x.detalle,x.codigo)||'und')).join('\n');
  }
  function repairTextarea(){
    const ta=byId('repuestosManual');if(!ta||!clean(ta.value))return false;
    const items=String(ta.value).split(/\r?\n/).map(parseLine).filter(Boolean);
    let changed=false;items.forEach(x=>{if(repairItem(x,null))changed=true;});
    const next=serialize(items);
    if(next!==ta.value){ta.value=next;changed=true;}
    return changed;
  }
  function repairFinalTable(){
    let changed=false,internal=[];
    try{internal=window.zgRepuestosTablaFinal&&typeof window.zgRepuestosTablaFinal.materiales==='function'?window.zgRepuestosTablaFinal.materiales():[];}catch(e){internal=[];}
    if(Array.isArray(internal))internal.forEach(x=>{if(repairItem(x,null))changed=true;});
    const rows=[...document.querySelectorAll('#repuestosSelectedList .zg-repuestos-table tbody tr')];
    rows.forEach((row,idx)=>{
      const detail=clean(row.querySelector('.zg-rep-detail2')&&row.querySelector('.zg-rep-detail2').value);
      const current=clean(row.querySelector('.zg-rep-code2')&&row.querySelector('.zg-rep-code2').textContent).replace(/^Sin código$/i,'');
      const r=resolve(detail,null,(internal[idx]&&internal[idx].codigo)||current);if(!r)return;
      const codeNode=row.querySelector('.zg-rep-code2');
      if(codeNode&&clean(codeNode.textContent).replace(/^Sin código$/i,'')!==r.codigo){codeNode.textContent=r.codigo;codeNode.classList.remove('empty','zg-v56-code-pending');changed=true;}
      const unitNode=row.querySelector('.zg-rep-unit2');if(unitNode&&!clean(unitNode.textContent)){unitNode.textContent=r.unidad;changed=true;}
      if(internal[idx]){if(clean(internal[idx].codigo)!==r.codigo){internal[idx].codigo=r.codigo;changed=true;}if(!clean(internal[idx].unidad))internal[idx].unidad=r.unidad;}
    });
    if(Array.isArray(window.repuestosSeleccionados))window.repuestosSeleccionados.forEach(x=>{if(repairItem(x,null))changed=true;});
    return changed;
  }
  function workList(){
    try{return Object.values((typeof state!=='undefined'&&state.selected)?state.selected:{});}catch(e){return [];}
  }
  function repairWorkMaterials(){
    let changed=false;
    const works=workList();
    works.forEach((work,widx)=>{
      ['repuestosTrabajo','materialesTrabajo','materiales','repuestos'].forEach(k=>{
        if(Array.isArray(work&&work[k]))work[k].forEach(x=>{if(repairItem(x,work))changed=true;});
      });
      if(work&&work.campos&&typeof work.campos==='object'){
        ['repuestosTrabajo','materialesTrabajo','materiales','repuestos'].forEach(k=>{if(Array.isArray(work.campos[k]))work.campos[k].forEach(x=>{if(repairItem(x,work))changed=true;});});
      }
    });
    const panels=[...document.querySelectorAll('#panels .panel')];
    works.forEach((work,widx)=>{
      const arr=Array.isArray(work&&work.repuestosTrabajo)?work.repuestosTrabajo:[];
      const rows=[...(panels[widx]?panels[widx].querySelectorAll('.zg-work-material-table tbody tr'):[])];
      rows.forEach((row,idx)=>{
        if(row.querySelector('.zg-work-material-empty'))return;
        const item=arr[idx];
        const detail=clean(row.querySelector('.detail')&&row.querySelector('.detail').value)||(item&&item.detalle)||'';
        const r=resolve(detail,work,item&&item.codigo);if(!r)return;
        if(item&&clean(item.codigo)!==r.codigo){item.codigo=r.codigo;changed=true;}
        const codeNode=row.querySelector('td[data-label="Código"] b,td:first-child b');if(codeNode&&clean(codeNode.textContent).replace(/^Sin código$/i,'')!==r.codigo){codeNode.textContent=r.codigo;changed=true;}
        const unitNode=row.querySelector('.unit');if(unitNode&&!clean(unitNode.textContent)){unitNode.textContent=r.unidad;changed=true;}
      });
    });
    return changed;
  }
  function persist(){
    try{if(window.zgRepuestosTablaFinal&&typeof window.zgRepuestosTablaFinal.guardar==='function')window.zgRepuestosTablaFinal.guardar();}catch(e){}
    try{if(typeof window.zgSyncWorkMaterialsForPdf==='function')window.zgSyncWorkMaterialsForPdf();}catch(e){}
    try{if(window.zgroupMarcarCambio)window.zgroupMarcarCambio();}catch(e){}
  }
  function sync(){
    if(running){queued=true;return false;}
    running=true;let changed=false;
    try{
      changed=repairTextarea()||changed;
      changed=repairFinalTable()||changed;
      changed=repairWorkMaterials()||changed;
      if(changed){persist();repairTextarea();}
    }finally{
      running=false;
      if(queued){queued=false;setTimeout(sync,30);}
    }
    return changed;
  }
  function schedule(ms){setTimeout(sync,ms||0);}
  function wrapApis(){
    const api=window.zgRepuestosTablaFinal;
    if(api&&typeof api.pintar==='function'&&!api.pintar.__zgV56){
      const old=api.pintar;const next=function(){const r=old.apply(this,arguments);schedule(20);return r;};next.__zgV56=true;api.pintar=next;
    }
    if(typeof window.zgLoadEditMaterials==='function'&&!window.zgLoadEditMaterials.__zgV56){
      const old=window.zgLoadEditMaterials;const next=function(){const r=old.apply(this,arguments);[20,120,350,900].forEach(schedule);return r;};next.__zgV56=true;window.zgLoadEditMaterials=next;
    }
    if(typeof window.renderPanels==='function'&&!window.renderPanels.__zgV56){
      const old=window.renderPanels;const next=function(){const r=old.apply(this,arguments);schedule(30);return r;};next.__zgV56=true;window.renderPanels=next;
    }
  }
  function startObserver(){
    if(observerStarted||!document.body)return;observerStarted=true;
    const obs=new MutationObserver(muts=>{
      const relevant=muts.some(m=>{
        const t=m.target&&m.target.nodeType===1?m.target:m.target&&m.target.parentElement;
        return t&&t.closest&&t.closest('#repuestosSelectedList,#panels,#repuestosCard');
      });
      if(relevant)schedule(40);
    });
    obs.observe(document.body,{childList:true,subtree:true});
  }
  function init(){
    wrapApis();startObserver();
    [20,100,300,700,1300,2200,3500,5200,7500,10000].forEach(schedule);
    [1200,3000,6000].forEach(ms=>setTimeout(wrapApis,ms));
  }
  ['pointerdown','mousedown','touchstart'].forEach(type=>document.addEventListener(type,ev=>{if(ev.target&&ev.target.closest&&ev.target.closest('#pdfBtn,#preBtn'))sync();},true));
  document.addEventListener('submit',sync,true);
  document.addEventListener('input',ev=>{if(ev.target&&ev.target.matches&&ev.target.matches('.zg-rep-detail2,.zg-work-material-table .detail,#repuestosManual'))schedule(100);},true);
  document.addEventListener('change',ev=>{if(ev.target&&['marcaEquipo','controladorEquipo','zgTipoEquipo'].includes(ev.target.id))schedule(20);},true);
  window.zgCorregirTodosLosCodigosMateriales=sync;
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init);else init();
  window.addEventListener('load',()=>{wrapApis();[50,250,800,1800,4000,8000].forEach(schedule);});
})();
