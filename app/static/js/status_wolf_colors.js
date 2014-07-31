/**
 * Author: Mark Troyer
 * Date: 29 April 2013
 */

var swcolors = {
	Reds: {
		1: ['#FF0000'],
		2: ['#FF0000', '#BF3030'],
		3: ['#FF0000', '#BF3030', '#A60000'],
		4: ['#FF0000', '#BF3030', '#A60000', '#FF4040'],
		5: ['#FF0000', '#BF3030', '#A60000', '#FF4040', '#FF7373']
	},
	Oranges: {
		1: ['#FF7400'],
		2: ['#FF7400', '#BF6F30'],
		3: ['#FF7400', '#BF6F30', '#A64A00'],
		4: ['#FF7400', '#BF6F30', '#A64A00', '#FF9540'],
		5: ['#FF7400', '#BF6F30', '#A64A00', '#FF9540', '#FFB173']
	},
	OrangeYellows: {
		1: ['#FFA900'],
		2: ['#FFA900', '#BF8F30'],
		3: ['#FFA900', '#BF8F30', '#A66E00'],
		4: ['#FFA900', '#BF8F30', '#A66E00', '#FFBE40'],
		5: ['#FFA900', '#BF8F30', '#A66E00', '#FFBE40', '#FFCF73']
	},
	Yellows: {
		1: ['#FFE800'],
		2: ['#FFE800', '#BFB230'],
		3: ['#FFE800', '#BFB230', '#A69700'],
		4: ['#FFE800', '#BFB230', '#A69700', '#FFEE40'],
		5: ['#FFE800', '#BFB230', '#A69700', '#FFEE40', '#FFF273']
	},
	YellowGreens: {
		1: ['#CFF700'],
		2: ['#CFF700', '#A3B92E'],
		3: ['#CFF700', '#A3B92E', '#87A000'],
		4: ['#CFF700', '#A3B92E', '#87A000', '#DDFB3F'],
		5: ['#CFF700', '#A3B92E', '#87A000', '#DDFB3F', '#E5FB71']
	},
	Greens: {
		1: ['#62E200'],
		2: ['#62E200', '#62AA2A'],
		3: ['#62E200', '#62AA2A', '#409300'],
		4: ['#62E200', '#62AA2A', '#409300', '#8BF13C'],
		5: ['#62E200', '#62AA2A', '#409300', '#8BF13C', '#A6F16C']
	},
	BlueGreens: {
		1: ['#00AE68'],
		2: ['#00AE68', '#21825B'],
		3: ['#00AE68', '#21825B', '#007143'],
		4: ['#00AE68', '#21825B', '#007143', '#36D695'],
		5: ['#00AE68', '#21825B', '#007143', '#36D695', '#60D6A7']
	},
	LtBlues: {
		1: ['#0B61A4'],
		2: ['#0B61A4', '#25567B'],
		3: ['#0B61A4', '#25567B', '#033E6B'],
		4: ['#0B61A4', '#25567B', '#033E6B', '#3F92D2'],
		5: ['#0B61A4', '#25567B', '#033E6B', '#3F92D2', '#66A3D2']
	},
	Blues: {
		1: ['#1B1BB3'],
		2: ['#1B1BB3', '#313186'],
		3: ['#1B1BB3', '#313186', '#090974'],
		4: ['#1B1BB3', '#313186', '#090974', '#4F4FD9'],
		5: ['#1B1BB3', '#313186', '#090974', '#4F4FD9', '#7373D9']
	},
	Violets: {
		1: ['#530FAD'],
		2: ['#530FAD', '#4F2982'],
		3: ['#530FAD', '#4F2982', '#330570'],
		4: ['#530FAD', '#4F2982', '#330570', '#8243D6'],
		5: ['#530FAD', '#4F2982', '#330570', '#8243D6', '#996AD6']
	},
	Magentas: {
		1: ['#AD009F'],
		2: ['#AD009F', '#82217A'],
		3: ['#AD009F', '#82217A', '#710067'],
		4: ['#AD009F', '#82217A', '#710067', '#D636C9'],
		5: ['#AD009F', '#82217A', '#710067', '#D636C9', '#D660CC']
	},
	CoolReds: {
		1: ['#E20048'],
		2: ['#E20048', '#AA2A53'],
		3: ['#E20048', '#AA2A53', '#93002F'],
		4: ['#E20048', '#AA2A53', '#93002F', '#F13C76'],
		5: ['#E20048', '#AA2A53', '#93002F', '#F13C76', '#F16C97']
	}
};

swcolors.Sequential = {
		1: swcolors.Reds[1].concat(swcolors.Oranges[1],
			swcolors.OrangeYellows[1],
			swcolors.Yellows[1],
			swcolors.YellowGreens[1],
			swcolors.Greens[1],
			swcolors.BlueGreens[1],
			swcolors.LtBlues[1],
			swcolors.Blues[1],
			swcolors.Violets[1],
			swcolors.Magentas[1],
			swcolors.CoolReds[1]),
		2: swcolors.Reds[2].concat(swcolors.Oranges[2],
			swcolors.OrangeYellows[2],
			swcolors.Yellows[2],
			swcolors.YellowGreens[2],
			swcolors.Greens[2],
			swcolors.BlueGreens[2],
			swcolors.LtBlues[2],
			swcolors.Blues[2],
			swcolors.Violets[2],
			swcolors.Magentas[2],
			swcolors.CoolReds[2]),
		3: swcolors.Reds[3].concat(swcolors.Oranges[3],
			swcolors.OrangeYellows[3],
			swcolors.Yellows[3],
			swcolors.YellowGreens[3],
			swcolors.Greens[3],
			swcolors.BlueGreens[3],
			swcolors.LtBlues[3],
			swcolors.Blues[3],
			swcolors.Violets[3],
			swcolors.Magentas[3],
			swcolors.CoolReds[3]),
		4: swcolors.Reds[4].concat(swcolors.Oranges[4],
			swcolors.OrangeYellows[4],
			swcolors.Yellows[4],
			swcolors.YellowGreens[4],
			swcolors.Greens[4],
			swcolors.BlueGreens[4],
			swcolors.LtBlues[4],
			swcolors.Blues[4],
			swcolors.Violets[4],
			swcolors.Magentas[4],
			swcolors.CoolReds[4]),
		5: swcolors.Reds[5].concat(swcolors.Oranges[5],
			swcolors.OrangeYellows[5],
			swcolors.Yellows[5],
			swcolors.YellowGreens[5],
			swcolors.Greens[5],
			swcolors.BlueGreens[5],
			swcolors.LtBlues[5],
			swcolors.Blues[5],
			swcolors.Violets[5],
			swcolors.Magentas[5],
			swcolors.CoolReds[5])
};

swcolors.Wheel = [];

swcolors.Wheel[1] = swcolors.Blues[1].concat(swcolors.Reds[1],
    swcolors.Yellows[1],
    swcolors.CoolReds[1],
    swcolors.YellowGreens[1],
	swcolors.Magentas[1],
    swcolors.Oranges[1],
    swcolors.Greens[1],
    swcolors.OrangeYellows[1],
	swcolors.BlueGreens[1],
	swcolors.Violets[1],
    swcolors.LtBlues[1]);

swcolors.Wheel[2] = swcolors.Wheel[1].concat(swcolors.Blues[2][1],
    swcolors.Reds[2][1],
    swcolors.Yellows[2][1],
    swcolors.CoolReds[2][1],
    swcolors.YellowGreens[2][1],
	swcolors.Magentas[2][1],
    swcolors.Oranges[2][1],
    swcolors.Greens[2][1],
    swcolors.OrangeYellows[2][1],
	swcolors.BlueGreens[2][1],
	swcolors.Violets[2][1],
    swcolors.LtBlues[2][1]);

swcolors.Wheel[3] = swcolors.Wheel[2].concat(swcolors.Blues[3][2],
    swcolors.Reds[3][2],
    swcolors.Yellows[3][2],
    swcolors.CoolReds[3][2],
    swcolors.YellowGreens[3][2],
	swcolors.Magentas[3][2],
    swcolors.Oranges[3][2],
    swcolors.Greens[3][2],
    swcolors.OrangeYellows[3][2],
	swcolors.BlueGreens[3][2],
	swcolors.Violets[3][2],
    swcolors.LtBlues[3][2]);

swcolors.Wheel[4] = swcolors.Wheel[3].concat(swcolors.Blues[4][3],
    swcolors.Reds[4][3],
    swcolors.Yellows[4][3],
    swcolors.CoolReds[4][3],
    swcolors.YellowGreens[4][3],
	swcolors.Magentas[4][3],
    swcolors.Oranges[4][3],
    swcolors.Greens[4][3],
    swcolors.OrangeYellows[4][3],
	swcolors.BlueGreens[4][3],
	swcolors.Violets[4][3],
    swcolors.LtBlues[4][3]);

swcolors.Wheel[5] = swcolors.Wheel[4].concat(swcolors.Blues[5][4],
    swcolors.Reds[5][4],
    swcolors.Yellows[5][4],
    swcolors.CoolReds[5][4],
    swcolors.YellowGreens[5][4],
	swcolors.Magentas[5][4],
    swcolors.Oranges[5][4],
    swcolors.Greens[5][4],
    swcolors.OrangeYellows[5][4],
	swcolors.BlueGreens[5][4],
	swcolors.Violets[5][4],
    swcolors.LtBlues[5][4]);

swcolors.Wheel_DarkBG = [];

swcolors.Wheel_DarkBG[1] = swcolors.Oranges[1].concat(swcolors.BlueGreens[1],
    swcolors.LtBlues[1],
	swcolors.OrangeYellows[1],
	swcolors.Magentas[1],
	swcolors.Yellows[1],
	swcolors.Reds[1],
	swcolors.YellowGreens[1],
	swcolors.CoolReds[1],
    swcolors.Greens[1]);

swcolors.Wheel_DarkBG[2] = swcolors.Wheel_DarkBG[1].concat(swcolors.Oranges[2][1],
    swcolors.BlueGreens[2][1],
	swcolors.LtBlues[2][1],
	swcolors.OrangeYellows[2][1],
	swcolors.Magentas[2][1],
	swcolors.Yellows[2][1],
	swcolors.Reds[2][1],
	swcolors.YellowGreens[2][1],
	swcolors.CoolReds[2][1],
    swcolors.Greens[2][1]);

swcolors.Wheel_DarkBG[3] = swcolors.Wheel_DarkBG[2].concat(swcolors.Oranges[3][2],
    swcolors.BlueGreens[3][2],
	swcolors.LtBlues[3][2],
	swcolors.OrangeYellows[3][2],
	swcolors.Magentas[3][2],
	swcolors.Yellows[3][2],
	swcolors.Reds[3][2],
	swcolors.YellowGreens[3][2],
	swcolors.CoolReds[3][2],
    swcolors.Greens[3][2]);

swcolors.Wheel_DarkBG[4] = swcolors.Wheel_DarkBG[3].concat(swcolors.Oranges[4][3],
    swcolors.BlueGreens[4][3],
	swcolors.LtBlues[4][3],
	swcolors.OrangeYellows[4][3],
	swcolors.Magentas[4][3],
	swcolors.Yellows[4][3],
	swcolors.Reds[4][3],
	swcolors.YellowGreens[4][3],
	swcolors.CoolReds[4][3],
    swcolors.Greens[4][3]);

swcolors.Wheel_DarkBG[5] = swcolors.Wheel_DarkBG[4].concat(swcolors.Oranges[5][4],
    swcolors.BlueGreens[5][4],
	swcolors.LtBlues[5][4],
	swcolors.OrangeYellows[5][4],
	swcolors.Magentas[5][4],
	swcolors.Yellows[5][4],
	swcolors.Reds[5][4],
	swcolors.YellowGreens[5][4],
	swcolors.CoolReds[5][4],
    swcolors.Greens[5][4]);
