export default function getUsername(user) {
    try{    
        fetch('api/check_session.php')
            .then(res => res.json())
            .then(data => {
                const userName = data.user.username;
            });
        }
    catch(error){
        console.log(error);
    }
}
