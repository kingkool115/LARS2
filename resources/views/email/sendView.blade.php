<!-- TODO: Den Email-Inhalt schöner machen.-->
<html>
To verify email
<a href="{{route('sendEmailDone',["email" => $user->email,"verifyToken" => $user->verifyToken])}}">
   Click here
</a>
</html>