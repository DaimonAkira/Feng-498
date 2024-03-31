/*
  Tahsin Can Koçum
*/

//const config = require('./config.json');
const ip = "172.20.10.7";
const port = ":9090";
const urll = 'ws://'+ip+port;
var np = import('jsnumpy');
x1=11;
y1=-11;
theta1=0*np.pi/180;
x2=32;
y2=-11;
theta2=55*np.pi/180;
amax=0.5;
Vmax=3;

let brakestatus = false;

 statuscontrol = function(){
  if (document.getElementById("status").innerHTML == "Connected"){
    document.getElementById("status").style.color="green";
  }else if (document.getElementById("status").innerHTML == "Error"){
    document.getElementById("status").style.color="red";
  }else{
    document.getElementById("status").style.color="purple";
  }
}

A  = np.array([ [1, x1, x1**2, x1**3],
  [1, x2, x2**2, x2**3],
  [0, 1,   2*x1, 3*x1**2],
  [0, 1,   2*x2, 3*x2**2]
  ]);

b = np.array([ [y1],
 [y2],
 [np.tan(theta1)],
 [np.tan(theta2)],               
 ]);

a_coef = np.linalg.inv(A) @ b;

a0 = a_coef[0];
a1 = a_coef[1];
a2 = a_coef[2];
a3 = a_coef[3];

X = np.linspace(x1, x2, 1000, endpoint= True);
Y = a3 * X**3 +  a2 * X**2 + a1*X + a0;

S = np.linspace(0,0, X.size) 
S[0] = 0
for i in range(1, X.size){
    dX = X[i] - X[i-1]
    dY = Y[i] - Y[i-1]
    dS = np.sqrt( dX**2 + dY**2)
    S[i] = S[i-1] + dS};

    parametrize = function(amax, Vmax, S){
    D = S[-1]
    t1 = Vmax / amax
    tf = D/Vmax + t1
    
    S1 = 0.5*amax*t1**2
    S2 = D - S1
    S3 = D
    
    T = np.linspace(0,0, S.size)
    for i in range(S.size){
        s = S[i]
        if s <= S1:
            T[i] = np.sqrt( 2 * s / amax)
            
        elif s > S1 and s <= S2:
            T[i] = t1 + (s-S1)/Vmax
            
        elif s > S2 and s <= S3:
            T[i] = tf - np.sqrt(2*(D-s)/amax)}
    
    return T
    }
  T=parametrize(amax,Vmax,S);



var ros = new ROSLIB.Ros({
    //url: 'ws://192.168.1.21:9090'
    url: urll
  });

  ros.on('connection', function () {
    document.getElementById("status").innerHTML = "Connected";
    statuscontrol();
    console.log("Ros connection established to " + urll);
  });

  ros.on('error', function (error) {
    document.getElementById("status").innerHTML = "Error";
    statuscontrol();
    console.log("Something went wrong at " + urll);
  });

  ros.on('close', function () {
    document.getElementById("status").innerHTML = "Closed";
    statuscontrol();
    console.log("Ros connection closed at " + urll);
  });

  var txt_listener = new ROSLIB.Topic({
    ros: ros,
    name: '/txt_msg',
    messageType: 'std_msgs/String'
  });

  txt_listener.subscribe(function (m) {
    document.getElementById("msg").innerHTML = m.data;
    move(1, 0);
  });

  cmd_vel_listener = new ROSLIB.Topic({
    ros: ros,
    name: "/cmd_vel",
    messageType: 'geometry_msgs/Twist'
  });
  cmd_camera_listener = new ROSLIB.Topic({
    ros: ros,
    name: "/eva_mars/camera_port_joint_position_controller/command",
    messageType: 'std_msgs/Float64'
  });
  sondaj1_listener = new ROSLIB.Topic({
    ros: ros,
    name: "/eva_mars/sondaj_joint_position_controller/command",
    messageType: 'std_msgs/Float64'
  });
  sondaj2_listener = new ROSLIB.Topic({
    ros: ros,
    name: "/eva_mars/sondaj2_joint_position_controller/command",
    messageType: 'std_msgs/Float64'
  });
  sondaj3_listener = new ROSLIB.Topic({
    ros: ros,
    name: "/eva_mars/sondaj3_joint_position_controller/command",
    messageType: 'std_msgs/Float64'
  });
  cmd_veltarget_listener = new ROSLIB.Topic({
    ros: ros,
    name: "/cmd_vel",
    messageType: 'geometry_msgs/Twist'
  });
  cameratoleft = function(ang){
    var twist = new ROSLIB.Message({
        data: 0.6
      });
    cmd_camera_listener.publish(twist);
  }
  cameratoright = function(){
    var twist = new ROSLIB.Message({
        data: -0.6
      });
    cmd_camera_listener.publish(twist);
  }
  cameratoreset = function(){
    var twist = new ROSLIB.Message({
        data: 0.0
      });
    cmd_camera_listener.publish(twist);
  }
  sondaj1out = function(){
    var twist = new ROSLIB.Message({
        data: -0.04
      });
    sondaj1_listener.publish(twist);
  }
  sondaj1in = function(){
    var twist = new ROSLIB.Message({
        data: 0.0
      });
    sondaj1_listener.publish(twist);
  }
  sondaj2out = function(){
    var twist = new ROSLIB.Message({
        data: -0.10
      });
    sondaj2_listener.publish(twist);
  }
  sondaj2in = function(){
    var twist = new ROSLIB.Message({
        data: 0.0
      });
    sondaj2_listener.publish(twist);
  }
  sondaj3out = function(){
    var twist = new ROSLIB.Message({
        data: -0.24
      });
    sondaj3_listener.publish(twist);
  }
  sondaj3in = function(){
    var twist = new ROSLIB.Message({
        data: 0.0
      });
    sondaj3_listener.publish(twist);
  }
  sondajallout = function(){
    sondaj1out(),
    sondaj2out(),
    sondaj3out()
  }
  sondajallin = function(){
    sondaj1in(),
    sondaj2in(),
    sondaj3in()
  }
  /*differtialbrake = function(){
    if (brakestatus){
      self.move(0, 0);
      brakestatus = false
    }else{
      brakestatus = false;
    }
  }*/


  move = function (linear, angular) {
    var twist = new ROSLIB.Message({
      linear: {
        x: linear,
        y: 0,
        z: 0
      },
      angular: {
        x: 0,
        y: 0,
        z: angular
      }
    });
    cmd_vel_listener.publish(twist);
  }

  

  createJoystick = function () {
    var options = {
      zone: document.getElementById('zone_joystick'),
      threshold: 0.1,
      position: { left: '15%', top: '38%' },
      mode: 'static',
      size: 150,
      color: '#000000',
    };

    manager = nipplejs.create(options);

    linear_speed = 0;
    angular_speed = 0;

    manager.on('start', function (event, nipple) {
      timer = setInterval(function () {
        move(linear_speed, angular_speed);
      }, 25);
    });

    manager.on('move', function (event, nipple) {
      max_linear = 1.0; // m/s
      max_angular = 0.5; // rad/s
      max_distance = 75.0; // pixels;
      linear_speed = Math.sin(nipple.angle.radian) * max_linear * nipple.distance/max_distance;
      angular_speed = -Math.cos(nipple.angle.radian) * max_angular * nipple.distance/max_distance;
    });

    manager.on('end', function () {
      if (timer) {
        clearInterval(timer);
      }
      self.move(0, 0);
    });
  }
  function todayDate(){
    var d = new Date();
    var n = d.getFullYear() + "  ";
    return document.getElementById("copydate").innerHTML = n;
  }
  window.onload = function () {
    createJoystick();
  }
  
